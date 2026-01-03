<?php
// app/Http/Controllers/Api/EnquiryController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Mail\EnquiryMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
class EnquiryController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title'   => 'nullable|string|min:3|max:100',
                'name'    => 'required|string|min:3|max:100',
                'email'   => 'required|email|max:150',
                'subject' => 'required|string|min:3|max:150',
                'message' => 'required|string|min:10',
            ]);
            $enquiry = [
                'title'   => $request->title,
                'name'    => $request->name,
                'email'   => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'ip'      => $request->ip(),
            ];
            Log::info('Enquiry received', $enquiry);
            Mail::to('rahulkumarmaurya464@gmail.com')
                ->send(new EnquiryMail($enquiry));
            Log::info('Enquiry mail sent successfully');
            return response()->json([
                'status'  => true,
                'message' => 'Enquiry submitted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Enquiry mail error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to submit enquiry. Please try again.'
            ], 500);
        }
    }	

    public function homeEnquiryStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'arrival'   => 'required|date',
                'departure' => 'required|date|after:arrival',
                'adult'     => 'required|integer|min:1',
                'phone'     => 'required|string|min:8|max:15',
            ]);
            $enquiry = [
                'arrival'   => $validated['arrival'],
                'departure' => $validated['departure'],
                'adult'     => $validated['adult'],
                'phone'     => $validated['phone'],
            ];
            Log::info('Enquiry received', $enquiry);
            Mail::to('rahulkumarmaurya464@gmail.com')
                ->send(new EnquiryMail($enquiry));
            return response()->json([
                'status'  => true,
                'message' => 'Enquiry submitted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Enquiry mail error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to submit enquiry. Please try again.'
            ], 500);
        }
    }

}
