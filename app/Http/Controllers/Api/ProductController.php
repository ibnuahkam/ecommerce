<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImages;
use App\Models\Roles;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'weight' => 'required|integer|min:1',
            'length' => 'nullable|integer|min:1',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'status' => 'nullable|in:draft,active,inactive',
            'thumbnail' => 'required|image',
            'images.*' => 'nullable|image'
        ]);

        DB::beginTransaction();

        try {
            $thumbnailPath = $request->file('thumbnail')
                ->store('products/thumbnails', 'public');

            $product = Product::create([
                'user_id' => auth()->id(),
                'role_id' => auth()->user()->role_id,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'category_id' => $request->category_id,
                'weight' => $request->weight,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'status' => $request->status ?? 'draft',
                'thumbnail' => $thumbnailPath,
                'sold_count' => 0
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products/images', 'public');

                    ProductImages::create([
                        'product_id' => $product->id,
                        'image_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product created successfully',
                'data' => $product->load('images', 'category')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $products = Product::with([
                'images:id,product_id,image_path',
                'user:id,name,email',
                'role:id,code'
            ])
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->get();

        $products = $products->map(function ($product) {

            return [
                'uuid' => $product->uuid,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'thumbnail_url' => $product->thumbnail
                    ? asset('storage/' . $product->thumbnail)
                    : null,
                'images' => $product->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_url' => asset('storage/' . $image->image_path)
                    ];
                }),
                'created_at' => $product->created_at
                    ->timezone('Asia/Jakarta')
                    ->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'List Products',
            'data' => $products
        ]);
    }

    public function show(Product $product)
    {
        $product->load([
            'images:id,product_id,image_path',
            'user.profile',
            'role:id,code',
            'category:id,name,slug'
        ]);

        $data = [
            'uuid' => $product->uuid,
            'slug' => $product->slug,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'sold_count' => $product->sold_count,

            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,

            'shipping' => [
                'weight' => $product->weight,
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
            ],

            'thumbnail_url' => $product->thumbnail
                ? asset('storage/' . $product->thumbnail)
                : null,

            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => asset('storage/' . $image->image_path)
                ];
            }),

            'seller' => $product->user ? [
                'id' => $product->user->id,
                'name' => $product->user->name,
                'email' => $product->user->email,
                'profile' => [
                    'city' => optional($product->user->profile)->city,
                    'province' => optional($product->user->profile)->province,
                    'phone' => optional($product->user->profile)->phone,
                    'address' => optional($product->user->profile)->address,
                    'avatar' => optional($product->user->profile)->avatar
                        ? asset('storage/' . $product->user->profile->avatar)
                        : null,
                ]
            ] : null,

            'created_at' => $product->created_at
                ->timezone('Asia/Jakarta')
                ->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Product detail',
            'data' => $data
        ]);
    }

}
