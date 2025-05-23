@extends('layouts.app')

@section('content')
<div class="risk-assessment-detail-page">
    <risk-assessment :assessment-id="{{ $assessmentId }}"></risk-assessment>
</div>
@endsection

@push('styles')
<style>
    .risk-assessment-detail-page {
        min-height: calc(100vh - 80px);
        width: 100%;
        padding: 20px;
        background-color: #f8f9fa;
    }
</style>
@endpush
