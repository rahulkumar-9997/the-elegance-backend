<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BannerVideos;
use App\Models\Blog;
use App\Models\BlogParagraphs;
use App\Models\BlogImages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with(['images', 'paragraphs']) ->orderBy('created_at', 'desc')
        ->paginate(20);
        return view('backend.pages.blog.index', compact('blogs'));
    }

    public function create()
    {
        return view('backend.pages.blog.create');
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,JPG|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'content' => 'required|string',
            'more_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,JPG|max:2048',
            'paragraphs_title' => 'nullable|array',
            'paragraphs_title.*' => 'nullable|string|max:255',
            'paragraphs_content' => 'nullable|array',
            'paragraphs_content.*' => 'nullable|string',
            'paragraphs_image' => 'nullable|array',
            'paragraphs_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,JPG|max:4096',
        ]);
        $destinationPath = public_path('upload/blog');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        DB::beginTransaction();
        try {
            if ($request->hasFile('main_image')) {
                $mainImage = $request->file('main_image');
                $titleSlug = Str::slug($validatedData['title']);
                $mainImageName =  $titleSlug. '-' . uniqid() . '.webp';
                $mainImagePath = $destinationPath . '/' . $mainImageName;
                $this->processAndSaveImage($mainImage, $mainImagePath);
            } else {
                $mainImageName = null;
            }
            /**unique slug */
            $slug = Str::slug($validatedData['title']);
            $uniqueSlug = $slug;
            $counter = 1;
            while (Blog::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug . '-' . $counter;
                $counter++;
            }
            /**unique slug */
            $blog = Blog::create([
                'title' => $validatedData['title'],
                'slug' => $uniqueSlug,
                'short_desc' => $validatedData['short_description'] ?? null,
                'content' => $validatedData['content'] ?? null,
                'meta_title' => $validatedData['meta_title'] ?? null,
                'meta_description' => $validatedData['meta_description'] ?? null,
                'featured_image' => $mainImageName,
                'content' => $validatedData['content'],
                'user_id' =>  Auth::check() ? Auth::user()->id : null,
                'status' => 'published',
            ]);
            if ($request->hasFile('more_image')) {
                foreach ($request->file('more_image') as $image) {
                    $titleSlug = Str::slug($validatedData['title']);
                    $additionalImageName =  $titleSlug. '-' . uniqid() . '.webp';
                    $additionalImagePath = $destinationPath . '/' . $additionalImageName;
                    $this->processAndSaveImage($image, $additionalImagePath);
                    BlogImages::create([
                        'blog_id' => $blog->id,
                        'image' => $additionalImageName,
                        'alt_text' => $validatedData['title'],
                    ]);
                }
            }
            if (!empty($request->paragraphs_title[0])) {
                if ($request->has('add_paragraphs') && $request->add_paragraphs == 1) {
                    foreach ($request->paragraphs_title as $index => $title) {
                        if (!empty($title)) {
                            $content = $request->paragraphs_content[$index];
                            $image = $request->file('paragraphs_image')[$index] ?? null;
                            $paragraphImageName = null;
                            if ($image) {
                                $paragraphImageName = $titleSlug . '-' . $index . '-' . uniqid() . '.webp';
                                $paragraphImagePath = $destinationPath . '/' . $paragraphImageName;
                                $this->processAndSaveImage($image, $paragraphImagePath);
                            }                        
                            BlogParagraphs::create([
                                'blog_id' => $blog->id,
                                'title' => $title,
                                'content' => $content,
                                'image' => $paragraphImageName,
                                'sort_order' => $index + 1,
                            ]);
                        }
                    }
                }
            }
            DB::commit();
            return redirect()->route('manage-blog.index')
                ->with('success', 'Blog created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($mainImagePath) && file_exists($mainImagePath)) {
                unlink($mainImagePath);
            }
            if (isset($additionalImagePath) && file_exists($additionalImagePath)) {
                unlink($additionalImagePath);
            }
            if (isset($paragraphImagePath) && file_exists($paragraphImagePath)) {
                unlink($paragraphImagePath);
            }
            return back()->withInput()
                ->with('error', 'Error creating blog: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $blog = Blog::with(['images', 'paragraphs'])->findOrFail($id);
        return view('backend.pages.blog.edit', compact('blog'));
    }

    public function update(Request $request, $id)
    {
        //dd($request->all());
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'main_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp,JPG|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'content' => 'required|string',
            'more_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,JPG|max:2048',
            'paragraphs_title' => 'nullable|array',
            'paragraphs_title.*' => 'nullable|string|max:255',
            'paragraphs_content' => 'nullable|array',
            'paragraphs_content.*' => 'nullable|string',
            'paragraphs_image' => 'nullable|array',
            'paragraphs_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,JPG|max:4096',
        ]);

        $destinationPath = public_path('upload/blog');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        DB::beginTransaction();
        try {
            $blog = Blog::findOrFail($id);
            if ($request->hasFile('main_image')) {
                if ($blog->featured_image && file_exists($destinationPath.'/'.$blog->featured_image)) {
                    unlink($destinationPath.'/'.$blog->featured_image);
                }                
                $mainImage = $request->file('main_image');
                $titleSlug = Str::slug($validatedData['title']);
                $mainImageName = $titleSlug.'-'.uniqid().'.webp';
                $mainImagePath = $destinationPath.'/'.$mainImageName;
                $this->processAndSaveImage($mainImage, $mainImagePath);
                $blog->featured_image = $mainImageName;
            }
            $blog->update([
                'title' => $validatedData['title'],
                'short_desc' => $validatedData['short_description'] ?? null,
                'meta_title' => $validatedData['meta_title'] ?? null,
                'meta_description' => $validatedData['meta_description'] ?? null,
                'content' => $validatedData['content'],
                'status' => 'published',
            ]);
            /*Delete image handal */
            /* First get all existing image IDs */
            $existingImageIds = $blog->images->pluck('id')->toArray();
            $submittedImageIds = $request->existing_more_images ?? []; 
            $imagesToDelete = array_diff($existingImageIds, $submittedImageIds);            
            if (!empty($imagesToDelete)) {
                $images = BlogImages::whereIn('id', $imagesToDelete)->get();
                foreach ($images as $image) {
                    if (file_exists($destinationPath.'/'.$image->image)) {
                        unlink($destinationPath.'/'.$image->image);
                    }
                    $image->delete();
                }
            }
            /*Delete image handal */
            
            if ($request->hasFile('more_image')) {
                foreach ($request->file('more_image') as $image) {
                    $titleSlug = Str::slug($validatedData['title']);
                    $additionalImageName = $titleSlug.'-'.uniqid().'.webp';
                    $additionalImagePath = $destinationPath.'/'.$additionalImageName;
                    $this->processAndSaveImage($image, $additionalImagePath);
                    BlogImages::create([
                        'blog_id' => $blog->id,
                        'image' => $additionalImageName,
                        'alt_text' => $validatedData['title'],
                    ]);
                }
            }
            if (!empty($request->paragraphs_title)) {
                if ($request->has('add_paragraphs') && $request->add_paragraphs == 1) {
                    $existingParagraphIds = $request->paragraph_ids ?? [];
                    BlogParagraphs::where('blog_id', $blog->id)
                        ->whereNotIn('id', $existingParagraphIds)
                        ->delete();
                    foreach ($request->paragraphs_title as $index => $title) {
                        if (!empty($title)) {
                            $content = $request->paragraphs_content[$index];
                            $image = $request->file('paragraphs_image')[$index] ?? null;
                            $paragraphId = $existingParagraphIds[$index] ?? null;
                            $paragraphData = [
                                'blog_id' => $blog->id,
                                'title' => $title,
                                'content' => $content,
                                'sort_order' => $index + 1,
                            ];
                            if ($image) {
                                $paragraphImageName = Str::slug($validatedData['title']).'-'.$index.'-'.uniqid().'.webp';
                                $paragraphImagePath = $destinationPath.'/'.$paragraphImageName;
                                $this->processAndSaveImage($image, $paragraphImagePath);
                                $paragraphData['image'] = $paragraphImageName;
                                if ($paragraphId && $request->existing_paragraphs_image[$index] && 
                                    file_exists($destinationPath.'/'.$request->existing_paragraphs_image[$index])) {
                                    unlink($destinationPath.'/'.$request->existing_paragraphs_image[$index]);
                                }
                            } elseif ($paragraphId && isset($request->existing_paragraphs_image[$index])) {
                                $paragraphData['image'] = $request->existing_paragraphs_image[$index];
                            }                        
                            if ($paragraphId) {
                                BlogParagraphs::where('id', $paragraphId)->update($paragraphData);
                            } else {
                                BlogParagraphs::create($paragraphData);
                            }
                        }
                    }
                }
            }
            DB::commit();
            return redirect()->route('manage-blog.index')
                ->with('success', 'Blog updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error updating blog: ' . $e->getMessage());
        }
    }

    private function processAndSaveImage($image, $savePath, $quality = 80){
        $img = Image::make($image->getRealPath());
        $img->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->encode('webp', $quality)->save($savePath);
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($blog->featured_image && file_exists(public_path('upload/blog/'.$blog->featured_image))) {
                unlink(public_path('upload/blog/'.$blog->featured_image));
            }
            foreach ($blog->images as $image) {
                if (file_exists(public_path('upload/blog/'.$image->image))) {
                    unlink(public_path('upload/blog/'.$image->image));
                }
                $image->delete();
            }
            foreach ($blog->paragraphs as $paragraph) {
                if ($paragraph->image && file_exists(public_path('upload/blog/'.$paragraph->image))) {
                    unlink(public_path('upload/blog/'.$paragraph->image));
                }
                $paragraph->delete();
            }
            $blog->delete();
            DB::commit();
            return redirect()->route('manage-blog.index')
                ->with('success', 'Blog deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting blog: ' . $e->getMessage());
        }
    }
}

