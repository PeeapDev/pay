<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __("QRPay - Money Transfer with QR Code") }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- favicon -->
    <link rel="shortcut icon" href="{{ get_fav($basic_settings) }}" type="image/x-icon">
    <!-- bootstrap css link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('public/frontend/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('public/frontend/') }}/css/checkout.css">

    <style>
        .nice-select .list {
            height: 200px;
            overflow-y: auto;
        }
        .nice-select .list::-webkit-scrollbar {
            height: 20px;
            width: 3px;
            background: #f1f1f1;
            border-radius: 10px;
        }

        .nice-select .list::-webkit-scrollbar-thumb {
            background: #999;
            border-radius: 10px;
        }

        .nice-select .list::-webkit-scrollbar-corner {
            background: #999;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-light">
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start pay page
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="pay-page">
    <div class="container">
        <div class="form-wrapper h- d-flex flex-column justify-content-center vh-100">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <form class="demo-payment-form" method="POST" action="{{ route('merchant.checkout.initiate.payment') }}">
                        @csrf
                        <div class="title-area mb-20">
                           <h3>{{ __("QRPAY Merchant Website") }}</h3>
                        </div>
                        <div class="form-group">
                            <label for="amount" class="form-label">{{ __("Pay Amount") }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" id="amount" name="amount" class="form--control" placeholder="Enter Amount...">
                                <select class="form--control nice-select" name="wallet">
                                    @foreach ($sender_wallets ?? [] as $data)
                                        <option value="{{  $data->code }}">{{  $data->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="form-text">{{ __("Any Website Example Checkout Page.") }}</small>
                        </div>
                        <button type="submit" class="btn--base w-100">{{ __("Pay Now") }}</button>
                        <div class="badge-area mt-20 text-center">
                            <span class="form-text powered-badge"> <a href="{{"https://qrpay.appdevs.net/"}}">{{ __("Powered by QRPay") }}</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End pay page
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


<ul class="bg-bubbles">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</ul>

<!-- jquery -->
<script src="{{ asset('public/frontend/') }}/js/jquery-3.5.1.js"></script>
<script src="{{ asset('public/frontend/js/jquery.nice-select.js') }}"></script>
<!-- bootstrap js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.js"></script>
@include('admin.partials.notify')

<script>
    $(".nice-select").niceSelect()
</script>

</body>
</html>
