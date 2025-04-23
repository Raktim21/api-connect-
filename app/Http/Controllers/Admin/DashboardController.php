<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\PdfParse;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;


class DashboardController extends Controller
{
    public function index(){

        if (request()->search && request()->search != null) {
            $data = Http::get('https://oga.fasah.sa/api/applicationSupport/api/trader?id='.request()->search)->json()['listImporterNebras']['importerNebras'];

            $keys = [
                "commrgstrtntype",
                "hqimprnbr",
                "imprexprtype",
                "arbcimprname",
                "busid",
                "busidbrnch",
                "busidexprddt",
                "msg"
            ];
            
            $user = User::where('id', Auth::user()->id)->first();
            $user->total_search = $user->total_search + 1;
            $user->save();
            
            if (empty($data)) {
                $final_datas = [];
            }else {
                
                $result = array_map(function($item) use ($keys) {
                    return array_intersect_key($item, array_flip($keys));
                }, $data);
        
                $final_datas  = $data = array_map("unserialize", array_unique(array_map("serialize", $result)));
            }
            
        }else {
            $final_datas = [];
        }
        
        return view('admin.index',compact('final_datas'));
    }



    public function importerIndex(Request $request){


        if (request()->search && request()->port != null) {
            
            $data = Http::get('https://oga.fasah.sa/api/applicationSupport/api/trader?importerNumber='.request()->search.'&port='. request()->port)->json();
            
            $key = [
                "hqimprnbr",
                "busid",
                "busidbrnch",
                "busidexprddt",
                "imprexprtype",
                "arbcimprname",
            ];

            // dd($data);
            $user = User::where('id', Auth::user()->id)->first();
            $user->total_search = $user->total_search + 1;
            $user->save();


            if (empty($data)) {
                $final_datas = [];
            }else {
                
                $result = array_map(function($item) use ($key) {
                    return array_intersect_key($item, array_flip($key));
                }, $data);
            
    
                $final_datas  = $data = array_map("unserialize", array_unique(array_map("serialize", $result)));
            }
        }else {
            $final_datas = [];
        }

        
        return view('admin.importer',compact('final_datas'));
    }


    public function fetchImporterData(Request $request)
    {

        if (Auth::user()->role !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'importer_ids' => 'required|array',
        ]);

        $user = Auth::user();
        $results = [];

        foreach ($request->importer_ids as $importerId) {
            $response = Http::get("https://oga.fasah.sa/api/applicationSupport/api/trader?importerNumber=$importerId&port={$request->port}")->json();
            $user->increment('total_search');

            if (!empty($response) && is_array($response)) {
                $item = $response[0];
                $results[] = [
                    'Importer ID' => $importerId,
                    'Registration Number' => $item['busid'] ?? '',
                    'Customer Name' => $item['arbcimprname'] ?? '',
                ];
            }
        }

        return response()->json($results);
    }
    // end fetch importer data

    // fetch importer data (dashboard)
    public function fetchImporterDataList(Request $request)
    {
        if (Auth::user()->role !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'importer_ids' => 'required|array',
        ]);

        $user = Auth::user();
        $results = [];

        foreach ($request->importer_ids as $importerId) {
            $response = Http::get("https://oga.fasah.sa/api/applicationSupport/api/trader?id={$importerId}")->json();

            $data = $response['listImporterNebras']['importerNebras'] ?? [];

            $user->increment('total_search');


            if (!empty($data) && is_array($data)) {
                $item = $data[0];
                $results[] = [
                    'Importer CR' => $importerId,
                    'Importer Number' => $item['hqimprnbr'] ?? '',
                    'Customer Name' => $item['arbcimprname'] ?? '',
                ];
            }
        }

        return response()->json($results);
    }


    public function invoiceSummary()
    {
        $parse_log = $this->get_parse_log();

        return view('admin.invoice-summary', compact('parse_log'));
    }


    public function processPdf(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:2048',
        ]);

        $response_to_return = [];
        $prompt = $this->get_prompt();
        // API Setup
        $apiKey = "AIzaSyDQX1jydOt4RPDTvqRwkubROaKj3vRIdrI";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";

        $file = $request->file('file');
        $page_no = intval($request->page_no);
        $pdfPath = $file->getPathname();

        $pdfReader = new Fpdi();
        $pageCount = $pdfReader->setSourceFile($pdfPath);

        // for ($pageNo = 1; $pageNo <= 1; $pageNo++) {
        $pdf = new Fpdi();
        // $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($pdfPath);
        $pdf->AddPage();

        $tplId = $pdf->importPage($page_no);
        $pdf->useTemplate($tplId, 0, 0, 210, 297);

        $tempFilePath = storage_path("app/temp_page_$page_no.pdf");
        $pdf->Output($tempFilePath, 'S');
        file_put_contents($tempFilePath, $pdf->Output('', 'S'));

        try {
            $base64Data = base64_encode(file_get_contents($tempFilePath));
        } catch (\Exception $e) {
            unlink($tempFilePath);
            return response()->json(['error' => 'Failed to process file: ' . $e->getMessage()], 500);
        }

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        [
                            "inlineData" => [
                                "mimeType" => "application/pdf",
                                "data" => $base64Data
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_SLASHES));

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        $text = isset($responseData['candidates'][0]['content']['parts'][0]['text'])
            ? $responseData['candidates'][0]['content']['parts'][0]['text']
            : null;
        if ($text) {
            $jsonData = str_replace(["```json", "```"], "", $text);
            $text = json_decode($jsonData, true);
        }

        $response_to_return = [
            'total_pages' => $pageCount,
            'current_page_no' => $page_no,
            'result' => $text
        ];
        unlink($tempFilePath);
        return response()->json($response_to_return);
        // }

    }


    public function get_prompt()
    {

        return 'Task: Extract structured invoice item details from the provided image or PDF.

        Requirements:
        - Your goal is to extract the itemized product data from the invoice as accurately as possible.

        Data Fields to Extract (for each item):
        - Item: Product description.
        - HS_Code: Tariff code (commodity code).
        - Country_of_Origin: Country of origin for each item.
        - Currency: Currency used in the invoice (e.g., USD, EUR, etc.).
        - Quantity: Total quantity of each item.
        - Unit_Price: Price per unit. Be careful — recognize both formats like "15.56" and "15,56" as valid numbers.
        - Total_Price: Net total for each item.

        Important:
        - Do not automatically convert numbers like "45,76" to "45.76".
        - Preserve the original decimal format (comma or dot) as written in the invoice.
        - If the value is written as "45,76" in the document, return it as "45,76" in the output.
        - Do not assume locale. Use the exact formatting found in the document text.

        Important Notes:
        - Some numbers may use commas instead of decimal points (e.g., "15,56" or "45,00").
        - Do not convert comma to dot. Preserve original number formatting as found in the invoice.

        Strict Output Format:
        Return the data ONLY as a JSON array of objects like the example below.

        [
          {
            "Item": "value",
            "HS_Code": "value",
            "Country_of_Origin": "value",
            "Currency": "value",
            "Quantity": "value",
            "Unit_Price": "value",
            "Total_Price": "value"
          }
          // more items if any
        ]';

        // old prompt
        return 'Task: Extract specific invoice details from the provided image and summarize them in a structured format.

        Data Extraction Requirements:
        Item - Extract the product description from the invoice.
        HS Code - Also known as the tariff code or commodity code, retrieve the corresponding value.
        Country of Origin - Extract the country of origin for each item.
        Currency - Identify the currency used in the invoice.
        Quantity - Retrieve the quantity for each item.
        Unit Price - Extract the price per unit for each item.
        Total Price - Extract the total price for each item (Net Value).
        Data Consolidation:
        If an invoice has multiple declared HS Codes, consolidate them under one line if the first six digits match.
        only return JSON array of objects
        Strict Output Format (JSON):
        [
        {
            "Item": "value",
            "HS_Code": "value",
            "Country_of_Origin": "value",
            "Currency": "value",
            "Quantity": "value",
            "Unit_Price": "value",
            "Total_Price": "value"
        },//add more if any]';
    }

    public function addPdfParseLog(Request $request)
    {
        $user_id = Auth::user()->id;
        $file_name = $request->file_name;
        $status = $request->status;
        $total_page = $request->total_page;

        $pdfParse = PdfParse::create([
            "user_id" => $user_id,
            "file_name" => $file_name,
            "status" => $status,
            "total_page" => $total_page,
        ]);

        $parse_log = $this->get_parse_log();

        return response()->json([
            "success" => true,
            "parse_log" => $parse_log
        ]);
    }

    public function get_parse_log()
    {
        return DB::SELECT("SELECT COUNT(pdf_parses.id) AS total_count, users.name
        FROM `pdf_parses`
        LEFT JOIN users on users.id = pdf_parses.user_id
        GROUP BY user_id;");
    }
}
