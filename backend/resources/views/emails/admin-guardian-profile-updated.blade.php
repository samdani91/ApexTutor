@php
  $isNid    = $updateType === 'nid_document';
  $typeLabel = $isNid ? 'NID document' : 'profile information';
@endphp

@extends('emails.layout', [
  'accentColor' => '#0f2e5c',
  'icon'        => $isNid ? '&#128196;' : '&#9998;',
  'headline'    => 'Guardian Profile Updated',
  'subheadline' => $guardianName . ' has updated their ' . $typeLabel,
  'preheader'   => $guardianName . ' updated their ' . $typeLabel . ' on TutorKhujo.',
])

@section('content')
<p style="margin:0 0 16px;font-size:15px;color:#0F2E5C;font-weight:700;">Hello {{ $adminName }},</p>
<p style="margin:0 0 16px;font-size:14px;color:#374151;line-height:1.7;">
  <strong style="color:#0F2E5C;">{{ $guardianName }}</strong> has updated their {{ $typeLabel }} on TutorKhujo.
</p>
@if($isNid)
<p style="margin:0 0 20px;font-size:14px;color:#374151;line-height:1.7;">
  A new NID document has been uploaded and may require verification. Please log in to the admin panel to review the guardian's profile.
</p>
@else
<p style="margin:0 0 20px;font-size:14px;color:#374151;line-height:1.7;">
  Their profile details have been updated. You can view the changes in the admin panel under Guardian accounts.
</p>
@endif
<p style="margin:0;padding-top:18px;border-top:1px solid #e5e7eb;font-size:13px;color:#6b7280;line-height:1.6;">
  This notification was sent because a guardian updated their account information.
</p>
@endsection
