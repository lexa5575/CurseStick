@extends('emails.layouts.modern')

@section('title', 'Zelle Payment Instructions - CruseStick')

@section('header', '💳 Zelle Payment Instructions')

@section('content')
    <h2 style="color: #2d3748 !important; font-size: 22px !important; font-weight: 600 !important;">Payment Details</h2>
    
    @if($totalAmount)
    <div class="info-box success">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">💰 Payment Amount</h4>
        <p style="color: #4a5568 !important; font-size: 18px !important; font-weight: 600 !important; text-align: center; margin: 10px 0;">Total: <strong style="color: #2d3748 !important; font-size: 20px !important;">${{ number_format($totalAmount, 2) }}</strong></p>
    </div>
    @endif
    
    <p style="color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">Please use the information below to complete your payment via Zelle.</p>
    
    <div class="info-box warning">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">Zelle Payment Address</h4>
        <div style="background-color: white; padding: 15px; border-radius: 6px; border: 2px dashed #4299e1; font-family: monospace; font-size: 16px; font-weight: bold; text-align: center; margin: 15px 0; color: #000000; word-break: break-all;">
            {{ $zelleAddress->address }}
        </div>
        <p style="color: #4a5568 !important; font-size: 12px !important; font-weight: normal !important; text-align: center;"><small>Registered email: {{ $zelleAddress->email }}</small></p>
        
        @if($zelleAddress->note)
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Note:</strong> {{ $zelleAddress->note }}</p>
        @endif
        
        @if($zelleAddress->note && str_contains(strtolower($zelleAddress->note), 'service'))
        <p style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important;"><strong style="color: #2d3748 !important; font-weight: 600 !important;">Important:</strong> When making the payment, please include <strong style="color: #2d3748 !important; font-weight: 600 !important;">service</strong> in the payment comment.</p>
        @endif
    </div>
    
    <div class="info-box">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important;">📱 Follow these steps to Send money with Zelle®:</h4>
        <ol style="color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important; line-height: 1.6; margin: 15px 0; padding-left: 20px;">
            <li style="margin-bottom: 8px;">Sign on and select Transfer and Pay on the desktop or Pay & Transfer on the mobile app.</li>
            <li style="margin-bottom: 8px;">Select Zelle®.</li>
            <li style="margin-bottom: 8px;">Select Send.</li>
            <li style="margin-bottom: 8px;">Select the recipient.</li>
            <li style="margin-bottom: 8px;">
                Enter the amount
                @if($totalAmount)
                    : <strong style="color: #2d3748 !important;">${{ number_format($totalAmount, 2) }}</strong>
                @endif
                .
            </li>
            <li style="margin-bottom: 8px;">Select Review.</li>
            <li style="margin-bottom: 8px;">Select Send.</li>
        </ol>
    </div>
    
    <div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #ffffff; border: 2px solid #c6f6d5; border-left: 4px solid #38a169; border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
        <h4 style="color: #2d3748 !important; font-size: 16px !important; font-weight: 600 !important; margin: 0 0 10px 0;">✅ Payment Confirmation</h4>
        <p style="margin: 0; color: #4a5568 !important; font-size: 14px !important; font-weight: normal !important; line-height: 1.5;">Once you complete the payment, your order will be processed and shipped within 24-48 hours.</p>
    </div>
    
    <p style="color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">If you have any questions about the payment process, please don't hesitate to contact our support team.</p>
    
    <p style="margin-top: 30px; color: #4a5568 !important; font-size: 16px !important; font-weight: normal !important;">
        Best regards,<br>
        <strong style="color: #2d3748 !important; font-weight: 600 !important;">The CruseStick Team</strong>
    </p>
@endsection