<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogImages;
use App\Models\BlogParagraphs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\ImageHelper;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with(['images', 'paragraphs'])->orderBy('created_at', 'desc')->paginate(20);
        return view('backend.pages.blog.index', compact('blogs'));
    }

    public function create()
    {
        return view('backend.pages.blog.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
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
        
        $directory = 'blog';
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }        
        DB::beginTransaction();        
        try {
            $mainImageName = null;
            if ($request->hasFile('main_image')) {
                $mainImage = $request->file('main_image');
                $titleSlug = Str::slug($validatedData['title']);
                $mainImageName = $titleSlug.'-'.uniqid().'.webp';
                $mainImagePath = $directory.'/'.$mainImageName;                
                ImageHelper::saveAsWebp(
                    $mainImage,
                    storage_path('app/public/'.$mainImagePath),
                    75
                );
            }
            $slug = Str::slug($validatedData['title']);
            $uniqueSlug = $slug;
            $counter = 1;
            while (Blog::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug.'-'.$counter;
                $counter++;
            }
            /** Create blog */
            $blog = Blog::create([
                'title' => $validatedData['title'],
                'slug' => $uniqueSlug,
                'short_desc' => $validatedData['short_description'] ?? null,
                'content' => $validatedData['content'] ?? null,
                'meta_title' => $validatedData['meta_title'] ?? null,
                'meta_description' => $validatedData['meta_description'] ?? null,
                'featured_image' => $mainImageName,
                'user_id' => Auth::check() ? Auth::user()->id : null,
                'status' => 'published',
            ]);
            if ($request->hasFile('more_image')) {
                foreach ($request->file('more_image') as $image) {
                    $additionalImageName = $titleSlug.'-'.uniqid().'.webp';
                    $additionalImagePath = $directory.'/'.$additionalImageName;
                    ImageHelper::saveAsWebp(
                        $image,
                        storage_path('app/public/'.$additionalImagePath),
                        75
                    );                    
                    BlogImages::create([
                        'blog_id' => $blog->id,
                        'image' => $additionalImageName,
                        'alt_text' => $validatedData['title'],
                    ]);
                }
            }
            if (!empty($request->paragraphs_title) && is_array($request->paragraphs_title)) {
                foreach ($request->paragraphs_title as $index => $title) {
                    if (!empty($title)) {
                        $content = $request->paragraphs_content[$index] ?? null;
                        $image = $request->file('paragraphs_image')[$index] ?? null;
                        $paragraphImageName = null;
                        
                        if ($image && $image->isValid()) {
                            $paragraphImageName = $titleSlug.'-'.$index.'-'.uniqid().'.webp';
                            $paragraphImagePath = $directory.'/'.$paragraphImageName;
                            
                            ImageHelper::saveAsWebp(
                                $image,
                                storage_path('app/public/'.$paragraphImagePath),
                                75
                            );
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
            
            DB::commit();

            return redirect()->route('manage-blog.index')
                ->with('success', 'Blog created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Blog update error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);
            if (isset($mainImagePath) && Storage::disk('public')->exists($mainImagePath)) {
                Storage::disk('public')->delete($mainImagePath);
            }
            return back()->withInput()->with('error', 'Error creating blog: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $blog = Blog::with(['images', 'paragraphs'])->findOrFail($id);
        return view('backend.pages.blog.edit', compact('blog'));
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
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

        $directory = 'blog';
        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }        
        DB::beginTransaction();        
        try {
            $blog = Blog::findOrFail($id);
            $titleSlug = Str::slug($validatedData['title']);
            if ($request->hasFile('main_image')) {
                if ($blog->featured_image && Storage::disk('public')->exists($directory.'/'.$blog->featured_image)) {
                    Storage::disk('public')->delete($directory.'/'.$blog->featured_image);
                }                
                $mainImage = $request->file('main_image');
                $mainImageName = $titleSlug.'-'.uniqid().'.webp';
                $mainImagePath = $directory.'/'.$mainImageName;                
                ImageHelper::saveAsWebp(
                    $mainImage,
                    storage_path('app/public/'.$mainImagePath),
                    75
                );                
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

            /* Handle existing image deletion */
            $existingImageIds = $blog->images->pluck('id')->toArray();
            $submittedImageIds = $request->existing_more_images ?? [];
            $imagesToDelete = array_diff($existingImageIds, $submittedImageIds);            
            if (! empty($imagesToDelete)) {
                $images = BlogImages::whereIn('id', $imagesToDelete)->get();
                foreach ($images as $image) {
                    if (Storage::disk('public')->exists($directory.'/'.$image->image)) {
                        Storage::disk('public')->delete($directory.'/'.$image->image);
                    }
                    $image->delete();
                }
            }
            /* End image deletion handling */
            if ($request->hasFile('more_image')) {
                foreach ($request->file('more_image') as $image) {
                    $additionalImageName = $titleSlug.'-'.uniqid().'.webp';
                    $additionalImagePath = $directory.'/'.$additionalImageName;                    
                    ImageHelper::saveAsWebp(
                        $image,
                        storage_path('app/public/'.$additionalImagePath),
                        75
                    );                    
                    BlogImages::create([
                        'blog_id' => $blog->id,
                        'image' => $additionalImageName,
                        'alt_text' => $validatedData['title'],
                    ]);
                }
            }
            if (!empty($request->paragraphs_title) && is_array($request->paragraphs_title)) {
                $existingParagraphIds = $request->paragraph_ids ?? [];
                BlogParagraphs::where('blog_id', $blog->id)
                    ->whereNotIn('id', $existingParagraphIds)
                    ->delete();                
                foreach ($request->paragraphs_title as $index => $title) {
                    if (!empty($title)) {
                        $content = $request->paragraphs_content[$index] ?? null;
                        $image = $request->file('paragraphs_image')[$index] ?? null;
                        $paragraphId = $existingParagraphIds[$index] ?? null;
                        
                        $paragraphData = [
                            'blog_id' => $blog->id,
                            'title' => $title,
                            'content' => $content,
                            'sort_order' => $index + 1,
                        ];
                        if ($image && $image->isValid()) {
                            $paragraphImageName = $titleSlug.'-'.$index.'-'.uniqid().'.webp';
                            $paragraphImagePath = $directory.'/'.$paragraphImageName;                            
                            ImageHelper::saveAsWebp(
                                $image,
                                storage_path('app/public/'.$paragraphImagePath),
                                75
                            );                            
                            $paragraphData['image'] = $paragraphImageName;
                            if ($paragraphId && isset($request->existing_paragraphs_image[$index])) {
                                $oldImage = $request->existing_paragraphs_image[$index];
                                if ($oldImage && Storage::disk('public')->exists($directory.'/'.$oldImage)) {
                                    Storage::disk('public')->delete($directory.'/'.$oldImage);
                                }
                            }
                        } elseif ($paragraphId && isset($request->existing_paragraphs_image[$index])) {
                            $paragraphData['image'] = $request->existing_paragraphs_image[$index];
                        } else {
                            $paragraphData['image'] = null;
                        }
                        if ($paragraphId) {
                            BlogParagraphs::where('id', $paragraphId)->update($paragraphData);
                        } else {
                            BlogParagraphs::create($paragraphData);
                        }
                    }
                }
            }            
            DB::commit();
            return redirect()->route('manage-blog.index')->with('success', 'Blog updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Blog update error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
                'blog_id' => $id,
            ]);
            return back()->withInput()->with('error', 'Error updating blog: '.$e->getMessage());
        }
    }    

    public function destroy($id)
    {
        $blog = Blog::with(['images', 'paragraphs'])->findOrFail($id);        
        DB::beginTransaction();        
        try {
            $directory = 'blog';
            if ($blog->featured_image && Storage::disk('public')->exists($directory.'/'.$blog->featured_image)) {
                Storage::disk('public')->delete($directory.'/'.$blog->featured_image);
            }     
            foreach ($blog->images as $image) {
                if ($image->image && Storage::disk('public')->exists($directory.'/'.$image->image)) {
                    Storage::disk('public')->delete($directory.'/'.$image->image);
                }
                $image->delete();
            }
            foreach ($blog->paragraphs as $paragraph) {
                if ($paragraph->image && Storage::disk('public')->exists($directory.'/'.$paragraph->image)) {
                    Storage::disk('public')->delete($directory.'/'.$paragraph->image);
                }
                $paragraph->delete();
            }
            $blog->delete();            
            DB::commit();
            return redirect()->route('manage-blog.index')
                ->with('success', 'Blog deleted successfully.');                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Blog deletion error: ' . $e->getMessage(), [
                'exception' => $e,
                'blog_id' => $id,
            ]);
            return back()->with('error', 'Error deleting blog: ' . $e->getMessage());
        }
    }
}
