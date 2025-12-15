<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Contract\Storage;


class ProductController extends Controller
{

    //FUNCION AUXILIAR PARA SUBIR Y OPTIMIZAR IMAGENES
    private function uploadToFirebase($file)
    {
        $storage = app(Storage::class);
        $bucket = $storage->getBucket('webeva-de45d.firebasestorage.app');
        $bucketName = $bucket->name();

        //Generar nombre unico
        $fileName = "products/" . time() . "_" . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . ".jpg";

        //OPTIMOZAR IMAGEN (800PX ANCHO, JPG, CALIDAD 80%)
        // Intervention Image v3: Usar ImageManager
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($file);

        $optmizedImage = $image->scale(width: 800)
            ->toJpeg(80);

        //CREAR ARCHIVO TEMPORAL
        $tempPath = tempnam(sys_get_temp_dir(), "product_img");
        file_put_contents($tempPath, $optmizedImage);

        //SUBIR A FIREBASE
        $stream = fopen($tempPath, "r");

        $bucket->upload($stream, [
            "name" => $fileName,
            "predefinedAcl" => "publicRead"
        ]);

        //LIMPIAR
        if (is_resource($stream))
            fclose($stream);
        unlink($tempPath);

        //RETORNAR URL PUBLICA
        return "https://storage.googleapis.com/" . $bucketName . "/" . $fileName;
    }

    private function deleteOldImage(?string $url)
    {
        if (empty($url)) {
            return;
        }

        try {
            $storage = app(Storage::class);
            $bucketName = $storage->getBucket('webeva-de45d.firebasestorage.app')->name();

            $prefix = 'https://storage.googleapis.com/' . $bucketName . '/';
            $path = str_replace($prefix, '', $url);

            if ($path === $url) {
                // Si la URL no contenía nuestro bucket, no intentamos borrar nada ajeno
                return;
            }

            $storage->getBucket()->object($path)->delete();
        } catch (\Exception $e) {
            // Si algo falla, lo ignoramos
        }
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                "message" => "Producto no encontrado",
                "status" => 404
            ], 404);
        }

        return response()->json([
            "product" => $product,
            "status" => 200
        ], 200);
    }

    public function index(Request $request)
    {
        $query = Product::query();

        //FILTROS
        if ($request->has("name")) {
            $query->where("name", "like", "%" . $request->name . "%");
        }

        if ($request->has("price")) {
            $query->where("price", $request->price);
        }

        // Filtro por precio mínimo
        if ($request->has("min_price")) {
            $query->where("price", ">=", $request->min_price);
        }

        // Filtro por precio máximo
        if ($request->has("max_price")) {
            $query->where("price", "<=", $request->max_price);
        }


        //Paginacion
        $products = $query->paginate(10);

        $data = [
            "products" => $products,
            "status" => 200
        ];

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "description" => "required",
            "price" => "required",
            "image" => "required|image|max:10240"
        ]);

        if ($validator->fails()) {
            $data = [
                "errors" => $validator->errors(),
                "status" => 400
            ];
            return response()->json($data, 400);
        }

        try {
            //SUBIR IMAGEN Y OBTENER URL
            $urlImage = $this->uploadToFirebase($request->file("image"));

            //CREAR PRODUCTO CON LA URL 
            $product = Product::create([
                "name" => $request->name,
                "description" => $request->description,
                "price" => $request->price,
                "url_image" => $urlImage
            ]);

            return response()->json([
                "message" => "Producto creado con exito",
                "status" => 201
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error creando producto: " . $e->getMessage());
            return response()->json([
                "message" => "Error al crear el producto: " . $e->getMessage(),
                "status" => 500
            ], 500);
        }



    }

    public function update(Request $request, $id)
    {
        //VALIDAR SI EXISTE PRODUCTO
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                "message" => "Producto no encontrado",
                "status" => 404
            ], 404);
        }

        //VALIDAR DATOS
        $validator = Validator::make($request->all(), [
            "name" => "sometimes|required",
            "description" => "sometimes|required",
            "price" => "sometimes|required",
            "image" => "sometimes|required|image|max:10240"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
                "status" => 400
            ], 400);
        }

        try {
            $dataToUpdate = $request->only(["name", "description", "price",]);

            //SI EL USUARIO ENVIA NUEVA IMAGEN SE PROCESA
            if ($request->hasFile("image")) {
                //BORRAR IMAGEN VIEJA
                $this->deleteOldImage($product->url_image);

                //SUBIR NUEVA IMAGEN
                $newUrl = $this->uploadToFirebase($request->file("image"));
                $dataToUpdate["url_image"] = $newUrl;
            }

            $product->update($dataToUpdate);

            return response()->json([
                "message" => "Producto actualizado con exito",
                "status" => 200
            ], 200);
        } catch (\Exception $e) {

        }
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                "message" => "Producto no encontrado",
                "status" => 404
            ], 404);
        }

        $this->deleteOldImage($product->url_image);

        $product->delete();

        return response()->json([
            "message" => "Producto eliminado con exito",
            "status" => 200
        ], 200);
    }
}
