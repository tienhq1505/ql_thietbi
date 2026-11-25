<x-layouts.print :title="$title">
    @if(count($labels) > 0)
    <div class="label-grid">
        @foreach($labels as $label)
        <div class="label-card">
            <div class="label-qr">
                {!! $label['svg'] !!}
            </div>
            <div class="label-info">
                <div class="label-code">{{ $label['code'] }}</div>
                <div class="label-url">/q/{{ $label['code'] }}</div>
                <div class="label-name">{{ $label['name'] }}</div>
                <div class="label-dept">📍 {{ $label['department'] }}</div>
                <div class="label-model">📋 {{ $label['model'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-message">
        <p>Không có thiết bị nào để in nhãn.</p>
    </div>
    @endif
</x-layouts.print>
