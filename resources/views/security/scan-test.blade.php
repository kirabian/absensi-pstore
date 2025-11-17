<!DOCTYPE html>
<html>
<head>
    <title>Scanner Test</title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
    <h1>Scanner Test</h1>
    <div id="reader" style="width: 500px; height: 500px; border: 2px solid red;"></div>
    
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            alert(`SCAN BERHASIL: ${decodedText}`);
        }
        
        // Simple scanner tanpa config complex
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 } 
            }, 
            false
        );
        
        html5QrcodeScanner.render(onScanSuccess, function() {});
    </script>
</body>
</html>