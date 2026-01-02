<?php
// app/Http/Controllers/Api/EnquiryController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Mail\EnquiryMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EnquiryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'nullable|string|min:3|max:100',
            'name'    => 'required|string|min:3|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|string|min:3|max:150',
            'message' => 'required|string|min:10',
        ]);

        $enquiry = Enquiry::create([
            'enquiry_for'=> $request->title(),
            'name' => $request->name(),       
            'email' => $request->email(),
            'subject' => $request->subject(),
            'message'=> $request->message(),
            'ip' => $request->ip(),
        ]);
        Mail::to('rahulkumarmaurya464@gmail.com')->send(new EnquiryMail($enquiry));
        return response()->json([
            'status'  => true,
            'message' => 'Enquiry submitted successfully'
        ], 200);
    }
}
