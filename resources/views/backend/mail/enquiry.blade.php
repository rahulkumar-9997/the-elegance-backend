<h2>New Enquiry</h2>

@if(!empty($enquiry['title']))
    <p><strong>Enquiry For:</strong> {{ $enquiry['title'] }}</p>
@endif

<p><strong>Name:</strong> {{ $enquiry['name'] }}</p>
<p><strong>Email:</strong> {{ $enquiry['email'] }}</p>
<p><strong>Subject:</strong> {{ $enquiry['subject'] }}</p>

<p><strong>Message:</strong></p>
<p>{{ $enquiry['message'] }}</p>

<p><small>IP Address: {{ $enquiry['ip'] }}</small></p>
