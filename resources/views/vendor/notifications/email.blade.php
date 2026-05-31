<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? config('app.name') }}</title>
<style>
  body { margin:0; padding:0; background:#f4f7fb; font-family: 'Segoe UI', Arial, sans-serif; color:#2d3748; }
  .wrapper { max-width:600px; margin:32px auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#1a56db 0%,#0e4ab8 100%); padding:32px 40px; text-align:center; }
  .header img { height:40px; margin-bottom:8px; }
  .header h1 { margin:0; color:#ffffff; font-size:22px; font-weight:700; letter-spacing:.5px; }
  .header p  { margin:4px 0 0; color:#bfdbfe; font-size:13px; }
  .body      { padding:36px 40px; }
  .greeting  { font-size:18px; font-weight:600; color:#1a56db; margin-bottom:16px; }
  .content   { font-size:15px; line-height:1.75; color:#4a5568; }
  .content p { margin:0 0 14px; }
  .btn       { display:inline-block; margin:20px 0; padding:13px 32px; background:#1a56db; color:#ffffff !important; text-decoration:none; border-radius:8px; font-size:15px; font-weight:600; letter-spacing:.3px; }
  .btn:hover { background:#1648c4; }
  .subcopy   { margin-top:28px; padding-top:20px; border-top:1px solid #e2e8f0; font-size:13px; color:#a0aec0; line-height:1.6; }
  .subcopy a { color:#1a56db; text-decoration:none; word-break:break-all; }
  .footer    { background:#f8fafc; padding:20px 40px; text-align:center; border-top:1px solid #e2e8f0; }
  .footer p  { margin:0; font-size:12px; color:#a0aec0; line-height:1.8; }
  .footer a  { color:#1a56db; text-decoration:none; }
  .badge     { display:inline-block; background:#dbeafe; color:#1a56db; border-radius:99px; padding:3px 12px; font-size:11px; font-weight:700; letter-spacing:.5px; margin-bottom:16px; text-transform:uppercase; }
  @media(max-width:600px){
    .header,.body,.footer{ padding-left:20px; padding-right:20px; }
    .header h1{ font-size:18px; }
  }
</style>
</head>
<body>
<div class="wrapper">

  {{-- HEADER --}}
  <div class="header">
    <h1>EduBridge</h1>
    <p>by KMG Vital Links</p>
  </div>

  {{-- BODY --}}
  <div class="body">

    <div class="badge">{{ config('app.name', 'EduBridge') }}</div>

    {{-- Greeting --}}
    @isset($greeting)
      <div class="greeting">{{ $greeting }}</div>
    @else
      <div class="greeting">
        @if ($level === 'error') @lang('Whoops!')
        @else @lang('Hello!')
        @endif
      </div>
    @endisset

    {{-- Intro lines --}}
    <div class="content">
      @foreach ($introLines as $line)
        <p>{!! \Illuminate\Mail\Markdown::parse($line) !!}</p>
      @endforeach

      {{-- Action button --}}
      @isset($actionText)
        <p style="text-align:center; margin:28px 0;">
          <a href="{{ $actionUrl }}" class="btn">{{ $actionText }}</a>
        </p>
      @endisset

      {{-- Outro lines --}}
      @foreach ($outroLines as $line)
        <p>{!! \Illuminate\Mail\Markdown::parse($line) !!}</p>
      @endforeach
    </div>

    {{-- Salutation --}}
    @if (!empty($salutation))
      <p style="margin-top:24px; font-size:14px; color:#718096;">{{ $salutation }}</p>
    @else
      <p style="margin-top:24px; font-size:14px; color:#718096;">
        @lang('Regards'),<br><strong>{{ config('app.name') }}</strong>
      </p>
    @endif

    {{-- Sub-copy action URL fallback --}}
    @isset($actionText)
      <div class="subcopy">
        @lang(
          "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your web browser:",
          ['actionText' => $actionText]
        )<br>
        <a href="{{ $actionUrl }}">{{ $actionUrl }}</a>
      </div>
    @endisset

  </div>

  {{-- FOOTER --}}
  <div class="footer">
    <p>
      &copy; {{ date('Y') }} EduBridge by KMG Vital Links &nbsp;&middot;&nbsp;
      <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
    </p>
    <p style="margin-top:4px;">
      You are receiving this email because you have an account on EduBridge.<br>
      If you did not request this, no action is required.
    </p>
  </div>

</div>
</body>
</html>
