<?php

require_once __DIR__ . '/../config/razorpay.php';

echo "<h2>SmartCart Razorpay Connection Test</h2>";

echo "<p><strong>Key ID:</strong> "
    . htmlspecialchars(RAZORPAY_KEY_ID)
    . "</p>";

echo "<p><strong>Secret:</strong> Configured</p>";


// ---------------------------------------------------------
// CHECK CURL
// ---------------------------------------------------------

if (!function_exists('curl_init')) {

    die(
        "<p style='color:red'>
        ERROR: PHP cURL is NOT enabled.
        </p>"
    );

}

echo "<p style='color:green'>
cURL: ENABLED
</p>";


// ---------------------------------------------------------
// CREATE TEST ORDER
// ---------------------------------------------------------

$url =
    'https://api.razorpay.com/v1/orders';


$data = [

    'amount' => 10000,

    'currency' => 'INR',

    'receipt' =>
        'smartcart_test_' . time()

];


$ch =
    curl_init($url);


curl_setopt_array(
    $ch,
    [

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_POST => true,

        CURLOPT_USERPWD =>
            RAZORPAY_KEY_ID .
            ':' .
            RAZORPAY_KEY_SECRET,

        CURLOPT_HTTPHEADER => [

            'Content-Type: application/json'

        ],

        CURLOPT_POSTFIELDS =>
            json_encode($data),

        CURLOPT_TIMEOUT => 30,

    ]
);


$response =
    curl_exec($ch);


$http_code =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$curl_error =
    curl_error($ch);


curl_close($ch);


// ---------------------------------------------------------
// DISPLAY RESULT
// ---------------------------------------------------------

echo "<hr>";

echo "<h3>HTTP Code:</h3>";

echo "<pre>";
echo htmlspecialchars(
    (string)$http_code
);
echo "</pre>";


if ($curl_error !== '') {

    echo "<h3 style='color:red'>cURL Error:</h3>";

    echo "<pre>";
    echo htmlspecialchars(
        $curl_error
    );
    echo "</pre>";

}


echo "<h3>Razorpay Response:</h3>";

echo "<pre style='
background:#f5f5f5;
padding:15px;
border-radius:5px;
'>";

echo htmlspecialchars(
    $response
);

echo "</pre>";


if (
    $http_code === 200
) {

    echo "
    <h2 style='color:green'>
        SUCCESS
    </h2>

    <p>
        Razorpay is connected correctly.
    </p>
    ";

} else {

    echo "
    <h2 style='color:red'>
        FAILED
    </h2>

    <p>
        Razorpay rejected the request.
        Check the response above.
    </p>
    ";

}

?>