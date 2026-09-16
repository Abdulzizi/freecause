<?php

return [
    'verify_external_image' => env('VERIFY_EXTERNAL_IMAGE', env('APP_ENV') !== 'testing'),
];
