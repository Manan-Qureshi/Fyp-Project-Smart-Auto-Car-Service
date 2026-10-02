<?php

$cloudUrl = env('CLOUDINARY_URL');
if (!$cloudUrl && env('CLOUDINARY_CLOUD_NAME') && env('CLOUDINARY_API_KEY') && env('CLOUDINARY_API_SECRET')) {
    $cloudUrl = 'cloudinary://' . env('CLOUDINARY_API_KEY') . ':' . env('CLOUDINARY_API_SECRET') . '@' . env('CLOUDINARY_CLOUD_NAME');
}

return [
    'cloud_url' => $cloudUrl,
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),
];
