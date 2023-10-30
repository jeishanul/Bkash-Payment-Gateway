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

    input {
        height: 2rem;
        border: 1px solid #e3e3e3;
        border-radius: 5px;
        padding: 5px 15px;
    }

    input:focus {
        outline: none !important;
        border-color: #719ECE;
        box-shadow: 0 0 10px #719ECE;
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

    .success-message {
        position: absolute;
        margin-bottom: 100px;
        color: green;
        background-color: #0080001a;
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
        <input type="number" name="amount" placeholder="Enter your amount">
        <button id="bKash_button" onclick="BkashPayment()">
            Pay
        </button>
    </div>
</body>

</html>

<script type="text/javascript">
    function BkashPayment() {
        // get bkash token
        $.ajax({
            url: "{{ route('bkash-get-token') }}",
            type: 'GET',
            contentType: 'application/json',
            success: function(data) {},
            error: function(err) {
                console.log(err);
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
                url: '{{ route('bkash-execute-payment') }}',
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
                            showErrorMessage(data);
                            bKash.execute().onError();
                        }
                    } else {
                        $.get('{{ route('bkash-query-payment') }}', {
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
        console.log(request);
        $.ajax({
            url: '{{ route('bkash-create-payment') }}',
            data: {
                "amount": 900,
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
                showErrorMessage(err.responseJSON);
                bKash.create().onError();
            }
        });
    }

    function BkashSuccess(data) {
        $.post('{{ route('bkash-success') }}', {
            payment_info: data
        }, function(res) {
            location.reload()
        });
    }

    function showErrorMessage(response) {
        let message = 'Unknown Error';

        if (response.hasOwnProperty('errorMessage')) {
            let errorCode = parseInt(response.errorCode);
            let bkashErrorCode = [2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014,
                2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026, 2027, 2028, 2029, 2030,
                2031, 2032, 2033, 2034, 2035, 2036, 2037, 2038, 2039, 2040, 2041, 2042, 2043, 2044, 2045, 2046,
                2047, 2048, 2049, 2050, 2051, 2052, 2053, 2054, 2055, 2056, 2057, 2058, 2059, 2060, 2061, 2062,
                2063, 2064, 2065, 2066, 2067, 2068, 2069, 503,
            ];

            if (bkashErrorCode.includes(errorCode)) {
                message = response.errorMessage
            }
        }

        // Swal.fire("Payment Failed!", message, "error");
    }
</script>
