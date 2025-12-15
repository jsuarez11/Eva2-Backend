<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Storage;
use Intervention\Image\Facades\Image;

class FirebaseStorageController extends Controller
{
    protected $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function uploadPublicOptimized(Request $request)
    {
        // 1. Validación
        $request->validate([
            'image' => 'required|image|max:10240', // Aceptamos hasta 10MB porque luego la reduciremos
        ]);

        try {
            $file = $request->file('image');
            $bucket = $this->storage->getBucket();

            // 2. Definir ruta y nombre en Firebase
            // Es buena práctica forzar la extensión a .jpg si vamos a optimizar a jpg
            $fileName = 'uploads/' . time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.jpg';

            // 3. Optimización con Intervention Image
            // Redimensionamos a un ancho máximo de 800px (mantiene el aspecto) y calidad 80%
            $optimizedImage = Image::make($file)
                ->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio(); // Mantiene proporción
                    $constraint->upsize();      // Evita que imágenes pequeñas se pixelen al agrandar
                })
                ->encode('jpg', 80); // Convertir a JPG con 80% de calidad

            // 4. Guardar temporalmente la imagen optimizada en el servidor local
            // Necesitamos un archivo físico o un stream para subirlo a Firebase
            $tempPath = tempnam(sys_get_temp_dir(), 'optimized_img');
            file_put_contents($tempPath, $optimizedImage);

            $stream = fopen($tempPath, 'r');

            // 5. Subir a Firebase con permisos PÚBLICOS
            $object = $bucket->upload($stream, [
                'name' => $fileName,
                'predefinedAcl' => 'publicRead' // <--- ESTA LÍNEA HACE LA IMAGEN PÚBLICA
            ]);

            // 6. Limpiar archivo temporal
            if (is_resource($stream)) {
                fclose($stream);
            }
            unlink($tempPath);

            // 7. Construir la URL Pública manualmente
            // Las URLs públicas de Firebase/Google Cloud tienen este formato estándar:
            $publicUrl = 'https://storage.googleapis.com/' . $bucket->name() . '/' . $fileName;

            return response()->json([
                'status' => 'success',
                'message' => 'Imagen optimizada y pública',
                'url' => $publicUrl
            ]);

        } catch (\Exception $e) {
            // Manejo de errores (por ejemplo, si falla la conexión a Google)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    //ELIMINAR IMAGENES
    public function deleteImage(Request $request)
    {
        $input = $request->input("path");

        // Lógica para extraer el path si viene la URL completa
        // Borramos la parte de "https://storage.googleapis.com/nombre-bucket/"
        $bucketName = $this->storage->getBucket()->name();
        $prefix = "https://storage.googleapis.com/{$bucketName}/";

        // Si el input empieza con la URL, la reemplazamos por vacío para dejar solo el path
        $path = str_replace($prefix, '', $input);

        try {
            $this->storage->getBucket()->object($path)->delete();
            return response()->json(["message" => "Imagen eliminada correctamente"]);
        } catch (\Exception $e) {
            return response()->json(["error" => "No se encontró el archivo o ya fue borrado"], 500);
        }
    }
}
