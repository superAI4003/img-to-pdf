<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use TCPDF;
use Intervention\Image\Facades\Image;
use GuzzleHttp\Client;
use Barryvdh\DomPDF\Facade\Pdf;

class UploadController extends Controller
{
 
    public function showUploadForm()
    {
        return view('upload');
    }

    public function uploadImages(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);
    
        $images = [];
        if($request->hasfile('images'))
        {
            foreach($request->file('images') as $file)
            {
                $name = time().rand(1,100).'.'.$file->extension();
                $filePath = public_path('images/' . $name);
                $file->move(public_path('images'), $name);  
    
                // Detect and correct the image orientation
                $this->correctOrientation($filePath);
    
                $images[] = $name;  
            }
        }
    
        return response()->json(['images' => $images]);
    }
    public function correctOrientation($imagePath)
    {
        $client = new Client();
        try {
            $response = $client->request('POST', 'http://127.0.0.1:8001/uploadfile', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($imagePath, 'r')
                    ]
                ]
            ]);
    
            $responseBody = json_decode($response->getBody(), true);
            $angle = $responseBody['Angle'];
            Log::info($angle);
            // Now you have the rotation angle, you can use it to correct the image orientation
            // ...
            $img = Image::make($imagePath);
            $img->rotate(-$angle); // Rotate in the opposite direction
            $img->save($imagePath); // Save the corrected image back to the same path    
            
        } catch (RequestException $e) {
            // Log the error message
            Log::error($e->getMessage());
        }
       
    }
   

    public function createPdf(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|string', // Adjust validation as needed
        ]);

        $images = $request->input('images');
        $html = '<html><head><style>
                    body, html { margin: 0; padding: 0; width: 100%; height: 100%; }
                    img { margin: 0; padding: 0; width: 100%; height: 100%; }
                 </style></head><body>';

        // Add each image to the HTML
        foreach ($images as $image) {
            $imagePath = public_path('images/' . $image);
            if (file_exists($imagePath)) {
                $html .= '<div style="page-break-after: always;"><img src="' . $imagePath . '"></div>';
            } else {
                $html .= '<div style="page-break-after: always;"><p>Image file not found: ' . $image . '</p></div>';
            }
        }

        $html .= '</body></html>';

        // Log the HTML content for debugging
        Log::info('HTML content for PDF: ' . $html);

        try {
            // Generate the PDF
            $pdf = Pdf::loadHTML($html);
            $pdfPath = 'pdfs/' . time() . '.pdf';

            // Ensure the directory exists
            if (!Storage::exists('pdfs')) {
                Storage::makeDirectory('pdfs');
            }

            Storage::put($pdfPath, $pdf->output());

            // Log the PDF path for debugging
            Log::info('PDF generated at: ' . storage_path('app/' . $pdfPath));

            return response()->json(['pdf_path' => $pdfPath]);
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Failed to create PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create PDF'], 500);
        }
    }

    public function saveToS3(Request $request)
    {
        $pdfPath = $request->input('pdf_path');
        if (!$pdfPath) {
            // Handle the error here, e.g., return a response or throw an exception
        }
        try {
            $s3Path = Storage::disk('s3')->putFile('pdfs', new \Illuminate\Http\File(storage_path('app/'.$pdfPath)));
            \Log::info($s3Path);
            // Ensure that $s3Path is not empty before trying to get the URL
            if ($s3Path) {
                $s3Url = Storage::disk('s3')->url($s3Path);
                return response()->json(['s3_url' => $s3Url]);
            } else {
                // Handle the error here, e.g., return a response or throw an exception
            }
        } catch (\Exception $e) {
            \Log::error('Failed to upload to S3: ' . $e->getMessage());
        }
    }
}
