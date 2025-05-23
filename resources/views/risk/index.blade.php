@extends('layouts.app')

@section('content')
<div class="risk-assessment-page">
    <risk-assessment-list></risk-assessment-list>
</div>
@endsection

@push('styles')
<style>
    .risk-assessment-page {
        min-height: calc(100vh - 80px);
        width: 100%;
        padding: 20px;
        background-color: #f8f9fa;
    }
</style>
@endpush
