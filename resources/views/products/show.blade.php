@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="product-image">
                <img src="{{ $productInfo['image_url'] ?? 'https://via.placeholder.com/400x400' }}" class="img-fluid" alt="{{ $productInfo['product_name'] ?? 'Product Image' }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="product-details">
                <h1 class="mb-4">{{ $productInfo['product_name'] ?? 'Unknown Product' }}</h1>
                <p>Efficiency Meets Affordability</p>
                <h2 class="text-success mb-3">Rs. {{ number_format($stats['min'], 2) }} <small class="text-muted"><del>Rs. {{ number_format($stats['max'], 2) }}</del></small></h2>
                <p>Tax included.</p>
                
                <div class="price-stats mb-4">
                    <div class="price-stat-item">
                        <i class="fas fa-clock"></i>
                        <h6>Average</h6>
                        <p>Rs. {{ number_format($stats['avg'], 2) }}</p>
                    </div>
                    <div class="price-stat-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <h6>Locations</h6>
                        <p>{{ $stats['count'] }}</p>
                    </div>
                    <div class="price-stat-item">
                        <i class="fas fa-battery-full"></i>
                        <h6>Savings</h6>
                        <p>{{ number_format(($stats['max'] - $stats['min']) / $stats['max'] * 100, 0) }}%</p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Type:</strong> {{ $productInfo['product_quantity'] ?? 'Unknown' }} {{ $productInfo['product_quantity_unit'] ?? '' }}
                </div>
                
                <button class="btn btn-primary btn-lg w-100 mb-2">Compare Prices</button>
                <button class="btn btn-outline-light btn-lg w-100">Find Nearest Store</button>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Price Comparison</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Store</th>
                            <th>Price (INR)</th>
                            <th>Date</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prices as $price)
                            <tr>
                                <td>{{ $price['store'] }}</td>
                                <td>
                                    @if($price['price'] == $stats['min'])
                                        <span class="text-success">Rs. {{ number_format($price['price'], 2) }} <i class="fas fa-check-circle"></i></span>
                                    @else
                                        Rs. {{ number_format($price['price'], 2) }}
                                    @endif
                                </td>
                                <td>{{ $price['date'] }}</td>
                                <td>{{ $price['location'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection