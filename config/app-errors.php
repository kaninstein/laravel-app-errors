<?php

return [
    'expose_debug' => env('APP_ERRORS_EXPOSE_DEBUG', env('APP_DEBUG', false)),
    'default_public_message' => 'Não foi possível concluir sua solicitação. Tente novamente.',
    'append_request_id_to_message' => false,
    'force_http_200_for_business' => env('APP_ERRORS_FORCE_HTTP_200_FOR_BUSINESS', false),
];

