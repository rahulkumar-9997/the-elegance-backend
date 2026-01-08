<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\NearByPlace;
use App\Models\Flyer;
use App\Models\Testimonial;
use App\Models\Banquet;
use App\Models\BanquetImage;
use App\Models\TafriLoungeImage;
use App\Models\Facility;
use App\Models\Album;
use App\Models\Gallery;
use App\Models\Blog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function singleVideo()
    {
        $banner = Banner::latest()->first();
        if (!$banner || !$banner->desktop_video) {
            return response()->json([
                'status' => false,
                'message' => 'No banner video found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => [
                'id'    => $banner->id,
                'video' => $banner->desktop_video,
            ]
        ]);
    }

    public function nearPlaceHome()
    {
        $nearByPlaces = NearByPlace::where('status', 1)
            ->orderBy('order')
            ->limit(8)
            ->get([
                'id',
                'title',
                'slug',
                'short_desc',
                'long_description',
                'image',
                'meta_title',
                'meta_description'
            ]);
        $nearByPlaces->transform(function ($item) {
            $item->image_url = asset('storage/nearby-places/' . $item->image);
            unset($item->image);
            return $item;
        });
        return response()->json([
            'status' => true,
            'count'  => $nearByPlaces->count(),
            'data'   => $nearByPlaces
        ]);
    }

    public function attractionHome()
    {
        $nearByPlaces = NearByPlace::where('status', 1)->where('attractions_status', 1)
            ->orderBy('order')
            ->limit(8)
            ->get([
                'id',
                'title',
                'slug',
                'short_desc',
                'long_description',
                'image',
                'meta_title',
                'meta_description'
            ]);
        $nearByPlaces->transform(function ($item) {
            $item->image_url = asset('storage/nearby-places/' . $item->image);
            unset($item->image);
            return $item;
        });
        return response()->json([
            'status' => true,
            'count'  => $nearByPlaces->count(),
            'data'   => $nearByPlaces
        ]);
    }

    public function flyersHome()
    {
        $flyers = Flyer::where('status', 1)
            ->orderBy('order')
            ->get([
                'id',
                'flyers_link',
                'image_file',
                'order'
            ]);

        $flyers->transform(function ($flyer) {
            return [
                'id' => $flyer->id,
                'link' => $flyer->flyers_link,
                'image_url' => asset('storage/flyers/' . $flyer->image_file),
                'order' => $flyer->order,
            ];
        });

        return response()->json([
            'status' => true,
            'count'  => $flyers->count(),
            'data'   => $flyers
        ]);
    }

    public function testimonialsHome()
    {
        $testimonials = Testimonial::where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
        $data = $testimonials->map(function ($t) {
            $visitDate = $t->visit_date
                ? Carbon::parse($t->visit_date)->format('F Y')
                : null;
            $ratingPercent = fn($rating) => $rating ? ($rating / 5) * 100 : 0;
            return [
                'id' => $t->id,
                'title' => $t->title,
                'guest_type' => $t->guest_type,
                'visit_date' => $visitDate,
                'review_text' => $t->review_text,
                'ratings' => [
                    [
                        'label' => 'Value',
                        'score' => number_format($t->value_rating, 1),
                        'percent' => $ratingPercent($t->value_rating),
                    ],
                    [
                        'label' => 'Rooms',
                        'score' => number_format($t->rooms_rating, 1),
                        'percent' => $ratingPercent($t->rooms_rating),
                    ],
                    [
                        'label' => 'Location',
                        'score' => number_format($t->location_rating, 1),
                        'percent' => $ratingPercent($t->location_rating),
                    ],
                    [
                        'label' => 'Cleanliness',
                        'score' => number_format($t->cleanliness_rating, 1),
                        'percent' => $ratingPercent($t->cleanliness_rating),
                    ],
                    [
                        'label' => 'Service',
                        'score' => number_format($t->service_rating, 1),
                        'percent' => $ratingPercent($t->service_rating),
                    ],
                    [
                        'label' => 'Sleep Quality',
                        'score' => number_format($t->sleep_quality_rating, 1),
                        'percent' => $ratingPercent($t->sleep_quality_rating),
                    ],
                ],
            ];
        });
        return response()->json([
            'status' => true,
            'count' => $data->count(),
            'data' => $data
        ]);
    }

    public function nearByPlaceList()
    {
        try {
            $nearByPlaces = NearByPlace::where('status', 1)
                ->orderBy('order')
                ->get([
                    'id',
                    'title',
                    'slug',
                    'short_desc',
                    'long_description',
                    'image',
                    'meta_title',
                    'meta_description'
                ]);
            if ($nearByPlaces->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No nearby places found.',
                    'count'   => 0,
                    'data'    => []
                ], 404);
            }
            $nearByPlaces->transform(function ($item) {
                $item->image_url = asset('storage/nearby-places/' . $item->image);
                unset($item->image);
                return $item;
            });
            return response()->json([
                'status' => true,
                'count'  => $nearByPlaces->count(),
                'data'   => $nearByPlaces
            ], 200);
        } catch (\Throwable $e) {
            Log::error('NearByPlace API Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch nearby places at the moment.',
                'data'    => []
            ], 500);
        }
    }

    public function nearByPlaceDetails($slug)
    {
        try {
            if (empty($slug)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid place identifier.',
                    'data'    => null
                ], 400);
            }
            $nearByPlace = NearByPlace::where('slug', $slug)
                ->where('status', 1)
                ->first([
                    'id',
                    'title',
                    'slug',
                    'short_desc',
                    'long_description',
                    'image',
                    'meta_title',
                    'meta_description'
                ]);
            if (!$nearByPlace) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Nearby place not found.',
                    'data'    => null
                ], 404);
            }
            $nearByPlace->image_url = asset('storage/nearby-places/' . $nearByPlace->image);
            unset($nearByPlace->image);
            $recentPosts = NearByPlace::where('status', 1)
                ->where('slug', '!=', $slug)
                ->orderByDesc('id')
                ->limit(4)
                ->get([
                    'id',
                    'title',
                    'slug',
                    'short_desc',
                    'image'
                ]);
            $recentPosts->transform(function ($item) {
                $item->image_url = asset('storage/nearby-places/' . $item->image);
                unset($item->image);
                return $item;
            });
            return response()->json([
                'status'       => true,
                'data'         => $nearByPlace,
                'recent_posts' => $recentPosts
            ], 200);
        } catch (\Throwable $e) {
            Log::error('NearByPlace Details API Error', [
                'slug'    => $slug,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch nearby place details at the moment.',
                'data'    => null
            ], 500);
        }
    }

    public function onexBanquet()
    {
        try {
            $banquet = Banquet::where('slug', 'onex-banquet')
                ->with(['images' => function ($query) {
                    $query->orderBy('order');
                }])
                ->first();
            if (!$banquet) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Banquet not found.',
                    'data'    => null
                ], 404);
            }
            $images = $banquet->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => asset('storage/banquets/' . $image->image_file),
                ];
            });
            return response()->json([
                'status' => true,
                'data'   => [
                    'id'          => $banquet->id,
                    'title'       => $banquet->title,
                    'slug'        => $banquet->slug,
                    'description' => $banquet->description ?? null,
                    'images'      => $images,
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Banquet Details API Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch banquet details at the moment.',
                'data'    => null
            ], 500);
        }
    }

    public function sapphireBanquet()
    {
        try {
            $banquet = Banquet::where('slug', 'sapphire-banquet')
                ->with(['images' => function ($query) {
                    $query->orderBy('order');
                }])
                ->first();
            if (!$banquet) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Banquet not found.',
                    'data'    => null
                ], 404);
            }
            $images = $banquet->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_url' => asset('storage/banquets/' . $image->image_file),
                ];
            });
            return response()->json([
                'status' => true,
                'data'   => [
                    'id'          => $banquet->id,
                    'title'       => $banquet->title,
                    'slug'        => $banquet->slug,
                    'description' => $banquet->description ?? null,
                    'images'      => $images,
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Banquet Details API Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch banquet details at the moment.',
                'data'    => null
            ], 500);
        }
    }

    public function tafriLoungeImages()
    {
        try {
            $tafriImageList = TafriLoungeImage::orderBy('order')->get();
            if ($tafriImageList->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No Tafri lounge images found.',
                    'data'    => []
                ], 404);
            }
            $images = $tafriImageList->map(function ($image) {
                return [
                    'id'        => $image->id,
                    'title'     => $image->title,
                    'order'     => $image->order,
                    'image_url' => asset('storage/tafri/' . $image->image_file),
                ];
            });
            return response()->json([
                'status' => true,
                'data'   => [
                    'total_images' => $images->count(),
                    'images'       => $images,
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Tafri Lounge Images API Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch Tafri lounge images at the moment.',
                'data'    => null
            ], 500);
        }
    }

    public function facilities()
    {
        try {
            $facilities = Facility::orderBy('id', 'desc')->get();
            if ($facilities->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No facilities found.',
                    'data'    => []
                ], 404);
            }
            $data = $facilities->map(function ($facility) {
                return [
                    'id'          => $facility->id,
                    'title'       => $facility->title,
                    'short_desc'  => $facility->short_desc,
                    'image_url'   => asset('storage/facilities/' . $facility->image),
                ];
            });
            return response()->json([
                'status' => true,
                'data'   => [
                    'total_facilities' => $data->count(),
                    'facilities'       => $data,
                ]
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Facilities API Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch facilities at the moment.',
                'data'    => null
            ], 500);
        }
    }

    public function albumGallery(Request $request)
    {
        try {
            $albums = Album::where('status', 1)
                ->orderBy('id', 'asc')
                ->get();
            if ($albums->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No albums found.',
                    'data'    => []
                ], 404);
            }
            if ($request->filled('album_id')) {
                $selectedAlbum = $albums->where('id', $request->album_id)->first();

                if (!$selectedAlbum) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Album not found.',
                        'data'    => null
                    ], 404);
                }
            } else {
                $selectedAlbum = $albums->first();
            }
            $galleries = Gallery::where('album_id', $selectedAlbum->id)
                ->orderBy('id', 'asc')
                ->get();
            $albumList = $albums->map(function ($album) use ($selectedAlbum) {
                return [
                    'id'        => $album->id,
                    'title'     => $album->title,
                    'is_active' => $album->id === $selectedAlbum->id,
                ];
            });
            $images = $galleries->map(function ($gallery) {
                return [
                    'id'          => $gallery->id,
                    'title'       => $gallery->title,
                    'description' => $gallery->description,
                    'image_url'   => asset('storage/gallery/' . $gallery->gallery_image),
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => [
                    'albums' => $albumList,
                    'selected_album' => [ 
                        'id'          => $selectedAlbum->id,
                        'title'       => $selectedAlbum->title,
                        'description' => $selectedAlbum->description,
                    ],
                    'total_images' => $images->count(),
                    'images'       => $images,
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Album Gallery API Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Unable to fetch album gallery at the moment.',
                'data'    => null
            ], 500);
        }
    }

    public function homeBlog()
    {
        try {
            $blogs = Blog::where('status', 'published')
                ->orderBy('id', 'desc')
                ->limit(4)
                ->get();
            if ($blogs->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No blogs found',
                    'data' => []
                ], 404);
            }
            $data = $blogs->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'short_desc' => $blog->short_desc,
                    'featured_image' => $blog->featured_image
                        ? asset('storage/blog/' . $blog->featured_image)
                        : null,
                    'created_at' => $blog->created_at->format('d M Y'),
                ];
            });
            return response()->json([
                'status' => true,
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Home Blog API Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Unable to fetch blogs',
                'data' => null
            ], 500);
        }
    }

    public function blogList(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $blogs = Blog::where('status', 'published')
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            if ($blogs->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No blogs found',
                    'data' => []
                ], 404);
            }

            $data = $blogs->getCollection()->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'short_desc' => $blog->short_desc,
                    'featured_image' => $blog->featured_image
                        ? asset('storage/blog/' . $blog->featured_image)
                        : null,
                    'created_at' => $blog->created_at->format('d M Y'),
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $blogs->currentPage(),
                    'last_page' => $blogs->lastPage(),
                    'per_page' => $blogs->perPage(),
                    'total' => $blogs->total(),
                    'has_more' => $blogs->hasMorePages(),
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Blog List API Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unable to fetch blogs',
                'data' => null
            ], 500);
        }
    }
    
    public function blogDetails($slug)
	{
		try {
			$blog = Blog::with(['images', 'paragraphs'])
				->where('slug', $slug)
				->where('status', 'published')
				->first();

			if (!$blog) {
				return response()->json([
					'status' => false,
					'message' => 'Blog not found',
					'data' => null
				], 404);
			}

			$recentPosts = Blog::where('status', 'published')
				->where('slug', '!=', $slug)
				->inRandomOrder()
				->limit(8)
				->get([
					'id',
					'title',
					'slug',
					'short_desc',
					'featured_image'
				]);

			$recentPosts->transform(function ($item) {
				return [
					'id' => $item->id,
					'title' => $item->title,
					'slug' => $item->slug,
					'short_desc' => $item->short_desc,
					'image_url' => $item->featured_image
						? asset('storage/blog/' . $item->featured_image)
						: null,
				];
			});

			return response()->json([
				'status' => true,
				'data' => [
					'id' => $blog->id,
					'title' => $blog->title,
					'slug' => $blog->slug,
					'short_desc' => $blog->short_desc,
					'content' => $blog->content,
					'meta_title' => $blog->meta_title,
					'meta_description' => $blog->meta_description,
					'featured_image' => $blog->featured_image
						? asset('storage/blog/' . $blog->featured_image)
						: null,
					'created_at' => optional($blog->created_at)->format('d M Y'),

					'images' => $blog->images->map(fn ($img) => [
						'id' => $img->id,
						'image' => asset('storage/blog/' . $img->image),
						'alt' => $img->alt_text,
					]),

					'paragraphs' => $blog->paragraphs->map(fn ($p) => [
						'title' => $p->title,
						'content' => $p->content,
						'image' => $p->image
							? asset('storage/blog/' . $p->image)
							: null,
					]),

					'recent_posts' => $recentPosts,
				]
			], 200);

		} catch (\Throwable $e) {
			Log::error('Blog Details API Error', [
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);

			return response()->json([
				'status' => false,
				'message' => 'Unable to fetch blog details',
				'data' => null
			], 500);
		}
	}





}
