<!DOCTYPE html>
<html lang="te">
<head>
    <meta charset="UTF-8">
    <title>Ration Card Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f8ff;
            padding: 40px;
            text-align: center;
        }
        input, button {
            padding: 10px;
            font-size: 16px;
            margin: 8px;
            width: 250px;
        }
        .result, .error {
            margin-top: 20px;
            font-weight: bold;
        }
        .result { color: green; }
		.result2 { font-size: 0px; }
        .error { color: red; }
        .copy-btn {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            padding: 10px 20px;
        }
        .copy-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>📑 ఆధార్ ద్వారా రేషన్ కార్డ్ వివరాలు తెలుసుకోండి</h2>
    <form method="POST" onsubmit="return validateForm()">
        <input type="text" name="aadhaar" id="aadhaar" maxlength="12" placeholder="Enter 12-digit Aadhaar" required pattern="\d{12}">
        <br>
        <button type="submit" name="search_ration">Search</button>
    </form>

    <script>
        function validateForm() {
            const aadhaar = document.getElementById('aadhaar').value.trim();
            const aadhaarPattern = /^\d{12}$/;
            if (!aadhaarPattern.test(aadhaar)) {
                alert("దయచేసి సరైన 12-అంకెల ఆధార్ నంబర్ నమోదు చేయండి.");
                return false;
            }
            return true;
        }

        function copyToClipboard() {
            const text = document.getElementById('rationText2').innerText;
            navigator.clipboard.writeText(text).then(function() {
                alert('📋 రేషన్ నంబర్ కాపీ అయింది!');
            }, function(err) {
                alert('❌ కాపీ చేయడంలో లోపం!');
            });
        }
    </script>

    <?php
    if (isset($_POST['search_ration'])) {
        $aadhaar = trim($_POST['aadhaar']);

        if (!preg_match("/^\d{12}$/", $aadhaar)) {
            echo "<p class='error'>❌ తప్పు ఆధార్ నంబర్! కేవలం 12 అంకెలు మాత్రమే ఉండాలి.</p>";
        } else {
            $url = "https://tgobmms.cgg.gov.in/checkersAadhaarDetails.action";
            $headers = [
                "User-Agent: Mozilla/5.0",
                "Accept: application/json",
                "Content-Type: application/json",
                "X-Requested-With: XMLHttpRequest",
                "Origin: https://tgobmms.cgg.gov.in",
                "Referer: https://tgobmms.cgg.gov.in/beneficiarySearchtoPublic.action",
                "Connection: keep-alive",
            ];

            $payload = json_encode([
                "aadhaar" => $aadhaar,
                "district" => "0",
                "tagname" => "aadhar",
                "corp_id" => "20",
                "sector" => "",
                "fin_year" => "2024-25",
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200) {
                $data = json_decode($response, true);
                if (is_array($data) && count($data) > 0) {
                    preg_match('/\b\d{12}\b/', json_encode($data), $matches);
                    if (!empty($matches)) {
                        $rationNumber = $matches[0];
                        echo "<p class='result' id='rationText'>✅ రేషన్ కార్డ్ నంబర్: <strong>$rationNumber</strong></p>";
						echo "<p class='result2' id='rationText2'><strong>$rationNumber</strong></p>";
						
                        echo "<button class='copy-btn' onclick='copyToClipboard()'>📋 Copy Ration Number</button>";
                    } else {
                        echo "<p class='error'>❌ ఈ ఆధార్ నంబర్‌కు రేషన్ కార్డ్ కనబడలేదు.</p>";
                    }
                } else {
                    echo "<p class='error'>⚠️ సర్వర్ నుంచి సరైన సమాచారం రాలేదు.</p>";
                }
            } else {
                echo "<p class='error'>🚫 API Error! HTTP Code: $http_code</p>";
            }
        }
    }
    ?>
</body>
</html>
