<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script id="myScript" src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js">
    </script>
    <title>Bkash Payment</title>
</head>
<style>
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    button {
        background-color: #585883;
        padding: 11px 25px;
        color: #ffffff;
        font-size: 15px;
        font-weight: 600;
        border: 1px solid #585883;
        border-radius: 5px;
    }

    button:hover {
        background-color: #5252a3;
        border: 1px solid #5252a3;
    }

    .error-message {
        position: absolute;
        margin-bottom: 100px;
        color: red;
        background-color: #ff00001c;
        padding: 10px 20px;
        border-radius: 5px;
    }

    span {
        position: absolute;
        margin-top: 52px;
        margin-left: -213px;
        color: red;
    }
</style>

<body>
    <div class="container">
        <div class="error-message" id="error-message" style="display: none">
            Please use valid credential for bkash
        </div>
        <img src="{{ asset('loader.gif') }}" width="50" style="display: none" id="loader">
        <button id="bKash_button" onclick="BkashPayment()">
            Pay with Bkash
        </button>
    </div>
</body>

</html>

<script type="text/javascript">
    function BkashPayment() {
        // get bkash token
        $.ajax({
            url: "{{ route('bkash.get.token') }}",
            type: 'GET',
            contentType: 'application/json',
            success: function(data) {
                if (data.status == 'fail') {
                    $('#error-message').show();
                } else {
                    $('#loader').show();
                    $('#bKash_button').hide();
                }
            }
        });
    }

    let paymentID = '';
    bKash.init({
        paymentMode: 'checkout',
        paymentRequest: {},
        createRequest: function(request) {
            setTimeout(function() {
                createPayment(request);
            }, 2000)
        },

        executeRequestOnAuthorization: function(request) {
            $.ajax({
                url: '{{ route('bkash.execute.payment') }}',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    "paymentID": paymentID
                }),
                success: function(data) {
                    if (data) {
                        if (data.paymentID != null) {
                            BkashSuccess(data);
                        } else {
                            bKash.execute().onError();
                        }
                    } else {
                        $.get('{{ route('bkash.query.payment') }}', {
                            payment_info: {
                                payment_id: paymentID
                            }
                        }, function(data) {
                            if (data.transactionStatus === 'Completed') {
                                BkashSuccess(data);
                            } else {
                                createPayment(request);
                            }
                        });
                    }
                },
                error: function(err) {
                    bKash.execute().onError();
                }
            });
        },
        onClose: function() {
            // for error handle after close bKash Popup
        }
    });

    function createPayment(request) {
        // Amount already checked and verified by the controller
        // because of createRequest function finds amount from this request
        request['amount'] = 99; // max two decimal points allowed
        $.ajax({
            url: '{{ route('bkash.make.payment') }}',
            data: {
                "amount": 99,
                "_token": "{{ csrf_token() }}",

            },
            type: 'GET',
            contentType: 'application/json',

            success: function(data) {
                if (data && data.paymentID != null) {
                    paymentID = data.paymentID;
                    bKash.create().onSuccess(data);
                } else {
                    bKash.create().onError();
                }
            },
            error: function(err) {
                bKash.create().onError();
            }
        });
    }

    function BkashSuccess(data) {
        $.post('{{ route('bkash.success') }}', {
            payment_info: data
        }, function(res) {
            location.reload()
        });
    }
</script>
