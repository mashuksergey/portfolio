<?php

use FileSystemService\UserInvoiceSystemService;
use Illuminate\Support\Facades\Input;

// get file from input
$file = Input::file('invoice');
if (!$file) {
    return response()->json(['error' => 'File is required'], 422);
}

$invoiceService = new UserInvoiceSystemService();
//save file to storage and get full name
$fullName = $invoiceService->saveFile($file);
if (!$fullName) {
    return response()->json(['error' => 'Can not save to db'], 422);
}

return response()->json(['file_url' => $invoiceService->getUriPath($fullName)]);

