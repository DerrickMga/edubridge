<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to PayFast…</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f8fafc; }
        .card { text-align: center; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 40px 48px; max-width: 420px; }
        .logo { font-size: 40px; margin-bottom: 16px; }
        h1 { font-size: 1.25rem; color: #1e293b; margin: 0 0 8px; }
        p { color: #64748b; font-size: 0.9rem; margin: 0 0 24px; }
        .spinner { display: inline-block; width: 32px; height: 32px; border: 3px solid #e2e8f0; border-top-color: #10b981; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .manual-btn { margin-top: 20px; display: inline-block; background: #10b981; color: #fff; font-weight: 600; font-size: 0.875rem; padding: 10px 24px; border-radius: 10px; text-decoration: none; cursor: pointer; border: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">🏦</div>
        <h1>Redirecting to PayFast</h1>
        <p>Please wait — you are being securely redirected to complete your payment.</p>
        <div class="spinner"></div>

        <form action="{{ $url }}" method="POST" id="payfast_form" style="display:none;">
            @foreach ($data as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
        </form>

        <br>
        <button class="manual-btn" onclick="document.getElementById('payfast_form').submit()">
            Continue to PayFast →
        </button>
    </div>

    <script>
        // Auto-submit after short delay
        setTimeout(function() {
            document.getElementById('payfast_form').submit();
        }, 1500);
    </script>
</body>
</html>
