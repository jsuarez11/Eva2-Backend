<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Validator;

class productController extends Controller
{
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
            "url_image" => "required|url"
        ]);
    
        if ($validator->fails()) {
            $data = [
                "errors" => $validator->errors(),
                "status" => 400
            ];
            return response()->json($data, 400);
        }

        $product= Product::create($request->only([
            "name","description","price","url_image"
        ]));

        return response()->json([
            "message" => "Producto creado con exito",
            "status"=> 201
        ], 201);
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request -> all(), [
            "name" => "sometime|required",
            "description" => "sometimes|required",
            "price" => "sometimes|required",
            "url_image" => "sometimes|required|url"
        ]);

        if($validator -> fails()){
            return response()-> json([
                "errors" => $validator->errors(),
                "status" => 400
            ], 400);
        }

        $product = Product::find($id);

        if(!$product){
            return response()-> json([
                "message" => "Producto no encontrado",
                "status" => 404
            ], 404);
        }

        $product -> update($request->only([
            "name", "description", "price", "url_image"
        ]));

        return response()->json([
            "message" => "Producto actualizado con exito",
            "status" => 200
        ], 200);
    }

    public function destroy($id){
        $product = Product::find($id);

        if(!$product){
            return response()->json([
                "message" => "Producto no encontrado",
                "status"=> 404
            ], 404);   
        }

        $product -> delete();

        return response()->json([
            "message" => "Producto eliminado con exito",
            "status" => 200
        ], 200);
    }
}
