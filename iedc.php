<?php
/**
 * Plugin Name: IEDC Electricity Payment
 */

 function iedc_payment()
{
    global $wpdb;
    $results = $wpdb->get_results( "SELECT payment_code FROM {$wpdb->prefix}transactions", OBJECT );
    $results = json_encode($results);
    ?>
        <form class="form-id">
        <label>Enter Code</label>
        <input type='text' name="code-value" value="" id="code-value" required />
        <button type='button' onclick="check()" id="formButton">Submit</button>
        
        </form>
        
        <div class="new-info">
        <h4>Customer Name: <span id="customer"></span></h4>
        <h4>Account No: <span id="account-no"></span></h4>
        <h4>Email: <span id="email"></span></h4>
        <h4>Contact No: <span id="contact"></span></h4>
        <h4>Meter Type: <span id="meter"></span></h4>
        <h4>Payment Ref No: <span id="ref"></span></h4>
        <h4>Map: <span id="map"></span></h4>
        <h4>Amount: <span id="amount"></span></h4>
        <!-- New addition to the html -->
        <h4>Commission: <span id="com"></span></h4>
        <h4>Amount to be Paid: <span id="amount-due"></span></h4>
        <h4> <span id="paid"></span></h4>
        <!-- end of additions -->
        <button type='button' onclick="payWithPaystack()" class="paystackButton">Pay Now</button> <button type="button" onclick="goBack()">Back</button>
        </div>
        
        <div class="error">
            <p>Error: <span id="error"></span></p>
            <button type="button" onclick="goBack()">Back</button>
        </div>

        <div class="callback">
            <div class="success">
                <h4>Transaction <span id="callback"></span></h4>
                <p>Transaction reference:  <span id="trans"></span></p>
                <p> <span id="trans-error"></span></p>
                
                <button type="button" onclick="goBack()">Back</button>
            </div>
            <div class="failed">
                <p>Transaction <span id="failed"></span></p>
            </div>
        </div>

        <div class="close">
            <h4>Transaction was not completed, window closed.</h4>
            <button type="button" onclick="goBack()">Back</button>
        </div>

        <script src="https://js.paystack.co/v1/inline.js"></script>
        <script type="text/javascript">
            var amount;
            var customerEmail;
            var customer; // initialize customer name variable
            var ref;
            var accountNo;
            var actualAmount;
            var splitCode;
            
            function check() {
                var paidCodes = JSON.parse('<?php echo $results; ?>');
                document.getElementById('formButton').disabled = true;
                var token = 'e1452383-3068-3743-95d4-3f88af73f014';
                var refCode = document.getElementById("code-value").value;   
                var activeButton = true;
                for(var i = 0; i < paidCodes.length; i++) {
                    if(paidCodes[i].payment_code == refCode) {
                        activeButton = false;
                        document.getElementById('paid').innerHTML = 'Already paid!';
                        break;
                    }
                } 

                //var url = "http://196.43.252.60/ie/mims/payreflookup2/v1/kycPaymentRefDetails?payRefNo="+refCode;
                var url = "https://api.ikejaelectric.com/ie/mims/payreflookup2/v1/kycPaymentRefDetails?payRefNo="+refCode;
                
                fetch(url, {
                    method: "GET",
                    headers: {
                        "Authorization": "Bearer "+token,
                    },
                })
                .then(response => {
                    if(response.ok){
                        return response.json();
                    }
                    document.getElementsByClassName('new-info')[0].style.display = 'none';
                    document.getElementsByClassName('form-id')[0].style.display = 'block';
                    
                    document.getElementById('error').innerHTML = response.statusText;
                    document.getElementsByClassName('error')[0].style.display = 'block';
                })
                .then(response => {
                    
                    if(response.res !== null) {
						//console.log(response.res.entry.transactionStatus);
                        document.getElementById('customer').innerHTML = response.res.entry.customerName;
                        document.getElementById('account-no').innerHTML = response.res.entry.accountNo;
                        document.getElementById('email').innerHTML = response.res.entry.email;
                        document.getElementById('contact').innerHTML = response.res.entry.contactNo;
                        document.getElementById('meter').innerHTML = response.res.entry.meterType;
                        document.getElementById('ref').innerHTML = response.res.entry.payRefNo;
                        document.getElementById('map').innerHTML = response.res.entry.map;
                        document.getElementById('amount').innerHTML = "N " + response.res.entry.amount;

                        document.getElementsByClassName('error')[0].style.display = 'none';
                        document.getElementsByClassName('new-info')[0].style.display = 'block';
                        document.getElementsByClassName('form-id')[0].style.display = 'none';
                        document.getElementsByClassName('paystackButton')[0].style.display = response.res.entry.transactionStatus == 'TREATED' || !activeButton ? 'none' : 'block';

                        // * changes start here
                        accountNo = response.res.entry.accountNo;
                        let decimalFee = 0.5 / 100;
                        commission = response.res.entry.amount * decimalFee;
                        let com = commission < 2000 ? '0.5%' : 'N '+2000;
                        document.getElementById('com').innerHTML = com;
                        amountWithCom = commission < 2000 ? (response.res.entry.amount / (1 - decimalFee)) + 0.01 : response.res.entry.amount + 2000;
                        amountWithCom = amountWithCom.toFixed(2);
                        amount = response.res.entry.amount;
                        document.getElementById('amount-due').innerHTML = "N " + amountWithCom;
                        customerEmail = response.res.entry.email;
                        customer = response.res.entry.customerName; // This for customer name
                        ref = response.res.entry.payRefNo;
                        actualAmount = response.res.entry.amount.toFixed(2);
                        
                        //switch split code - newly added
                        switch (response.res.entry.map) {
                            case "MOJEC":
                                splitCode = 'ACCT_kut0u07ldvs8617';
                            break;
							case "MBH":
                                splitCode = 'ACCT_jx15kkg9s4s1bf0';
                            break;
							case "ARIES":
                                splitCode = 'ACCT_v346dylqohpsebi';
                            break;	
                            case "NHC":
                                splitCode = 'ACCT_43cyyvqqba4ol2l';
                            break;
                            case "CIG":
                                splitCode = 'ACCT_8obvonvlpnbl1nw';
                            break;
                            case "BPS":
                                splitCode = 'ACCT_f1stg9u2qeceyiw';
                            break;
                        }

                    // * changes end here
                    } else {
                        document.getElementsByClassName('new-info')[0].style.display = 'none';
                        document.getElementsByClassName('form-id')[0].style.display = 'none';
                        
                        document.getElementById('error').innerHTML = 'Information not found for this payment reference.';
                        document.getElementsByClassName('error')[0].style.display = 'block';
                    }
                    
                })
                .catch(err=> {
                    console.log(err)
                });
                
            }

            function payWithPaystack() {
                var handler = PaystackPop.setup({
                    key: 'pk_live_664d195557591a124e298de15a9f3ccd3f4ac1a9', // the key has been changed to iedc's test key
                    email: customerEmail.trim(),
                    amount: actualAmount * 100,
                    //reference: ref,
                    ref: ref,
                    split_code: splitCode, //splitcode now as var
                    metadata: {
                        custom_fields: [
                            {
                                "display_name":"Customer Name",
                                "variable_name":"customer_name",
                                "value": customer
                            },
                            {
                                "display_name":"Payment Reference",
                                "variable_name":"pay_ref",
                                "value": ref
                            },
                            {
                                "display_name":"Account No",
                                "variable_name":"account_no",
                                "value": accountNo
                            }
                        ]
                    },
                    callback: function (res) {
                        /*
                        document.getElementsByClassName('error')[0].style.display = 'none';
                        document.getElementsByClassName('new-info')[0].style.display = 'none';
                        document.getElementsByClassName('form-id')[0].style.display = 'none';

                        if(res.status == 'success') {
                            document.getElementsByClassName('callback')[0].style.display = 'block';
                            document.getElementsByClassName('success')[0].style.display = 'block';
                            document.getElementById('callback').innerHTML = res.message;
                            document.getElementById('trans').innerHTML = res.reference;
                            updateCustomerPaymentStatus(ref, res.reference);
                        } else {
                            document.getElementsByClassName('failed')[0].style.display = 'block';
                            document.getElementById('failed').innerHTML = res.message;
                        }
                        */
                       //alert(res.status);
                        if(res.status == 'success') {
                            updateCustomerPaymentStatus(res.reference);
                            window.location.assign('/success?ref='+res.reference);
                        } else {
                            document.getElementsByClassName('failed')[0].style.display = 'block';
                            document.getElementById('failed').innerHTML = res.message;
                        }
                    },
                    onClose: function () {
                        document.getElementsByClassName('error')[0].style.display = 'none';
                        document.getElementsByClassName('new-info')[0].style.display = 'none';
                        document.getElementsByClassName('form-id')[0].style.display = 'none';
                        document.getElementsByClassName('close')[0].style.display = 'block';
                    }
                });
                handler.openIframe();
            }

            function goBack() {
                window.location.replace('/meterfee');
            }

            function updateCustomerPaymentStatus(payRef, transRef) {
                var token = 'e1452383-3068-3743-95d4-3f88af73f014';
                var xhttp = new XMLHttpRequest();
                var body = `<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:com='com.ikejaelectric'>
                            <soapenv:Header/>
                            <soapenv:Body>
                            <com:updatePaymentRefDetails>
                            <com:paymentTransactionReferenceId>`+transRef+`</com:paymentTransactionReferenceId>
                            <com:payRefNo>`+payRef+`</com:payRefNo>
                            </com:updatePaymentRefDetails>
                            </soapenv:Body>
                            </soapenv:Envelope>`;
                xhttp.open("POST", "https://api.ikejaelectric.com/ie/payrefdetailsupdate/v1", true);

                xhttp.setRequestHeader("Content-type", "text/xml");
                xhttp.setRequestHeader("SOAPAction", "");
                xhttp.setRequestHeader("Authorization", "Bearer "+ token);

                xhttp.send(body);

                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 202) {
                        return;
                    }
                    document.getElementById('trans').innerHTML = "Please contact admin immediately.";
                }
            }

    </script>
    <style>
        .new-info, .error, .callback, .close, .failed {
            display: none;
        }
    </style>
<?php
}
add_shortcode('iedc_payment_form', 'iedc_payment');

//Success response page log transaction details
function iedcPaystackCallBack()
{
    global $wpdb;
    if(!array_key_exists("ref",$_GET)) {
        return exit(404);
    }
    $ref = $_GET['ref'];
    $checkIfTransExists = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}transactions where ref_id = '".$ref."'", OBJECT );

    echo '<script>
            function goBack() {
                window.location.replace("/meterfee");
            }
        </script>';
    
    if(count($checkIfTransExists) > 0) {
        echo 'Transaction Reference already exist! <br /><button type="button" onclick="goBack()">Back</button>';
        return;
    }

    //Verify transaction on Paystack 
    $verifyTransactionUrl = "https://api.paystack.co/transaction/verify/$ref";
    $secretKey = 'sk_live_5fda1453bbeb44de4d7ce077f72880905221a613';
    $curl = curl_init("$verifyTransactionUrl");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$secretKey,
            ));

    $response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($response);

    //Prepare transaction info to be stored in DB 
    $customer = $result->data->metadata->custom_fields[0]->value;
    $payCode = $result->data->metadata->custom_fields[1]->value;
    $transRef = $result->data->reference;
    $amount = $result->data->amount;
    $paystackTransDate = date('Y-m-d h:i:s', strtotime($result->data->transaction_date));

    if($result->status == false) {
        echo $result->message;
        echo '<button type="button" onclick="goBack()">Back</button><br />';
        return;
    } 
	$table_name = $wpdb->prefix . 'transactions';
    $transactions = $wpdb->insert($table_name, 
            array('ref_id' => $transRef, 
                'payment_code' =>  $payCode,
                'customer' =>  $customer,
                'amount' =>  $amount,
                'paystack_trans_date' =>  $paystackTransDate,
                'source' =>  'website'
                ), array( '%s', '%s', '%s', '%s', '%s', '%s'));
    if($transactions) {
        echo '<div class="success">
        <h4>Transaction Approved</h4>
        <p>Transaction reference:  '.$transRef.'</p>
        <p> <span id="trans-error"></span></p>
        <button type="button" onclick="history.back()">Back</button>
    </div>';
        
    }
}
add_shortcode('iedc_returnUrl', 'iedcPaystackCallBack');

// Admin Menu for Transaction
function iedcTransactionsAdminMenu() 
{
	add_menu_page( 'IEDC Meter Payment Transactions', 'IEDC Meter Payments', 'manage_options', 'iedc/transactions-admin.php', 'iedcTransactionAdminPage', 'dashicons-money-alt', 6  );
}
add_action( 'admin_menu', 'iedcTransactionsAdminMenu' );

// Transaction Admin Page
function iedcTransactionAdminPage()
{
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}transactions ORDER BY id DESC", OBJECT ); 
    if(count($results) > 0) {
?>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12" style="margin-top: 50px;">
                    <h4>IEDC Meter Payments</h4> 
                    <table id="transac" class="table table-hover js-basic-example dataTable table-custom table-striped m-b-0 c_list">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Payment Code</th>
                                <th>Transaction ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
								<th>Source</th>
                                <th>Paystack Trans Date</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
        $c=0;
        foreach($results as $result) { 
            $c++;
?>
                        <tr>
                            <td><?php echo $c; ?></td>  
                            <td><?php echo $result->payment_code; ?></td>
                            <td><?php echo $result->ref_id; ?></td>
                            <td><?php echo $result->customer; ?></td>
                            <td><?php echo '₦ '. $result->amount / 100; ?></td>
							<td><?php echo $result->source; ?></td>
                            <td><?php echo $result->paystack_trans_date; ?></td>
                        </tr>
<?php
        }
?>      
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#transac').DataTable();
            });
        </script>
<?php
    } else {
?>
        <div class="col-md-12">No records found</div>
<?php
    }
}

// Paystack Webhook Url/Page
function iedc_webHook()
{
    global $wpdb;
    // only a post with paystack signature header gets our attention
    if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST' ) || !array_key_exists('x-paystack-signature', $_SERVER) ) 
    exit();

    $input = @file_get_contents("php://input");
    define('PAYSTACK_SECRET_KEY','SECRET_KEY');
    // validate event do all at once to avoid timing attack
    if($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY))
    exit();

    http_response_code(200);
    // parse event (which is json string) as object
    // Do something - that will not take long - with $event
    $event = json_decode($input);

    //store event in db
    if($event->event == 'charge.success') {
        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}transactions WHERE ref_id = '".$event->data->reference."'", OBJECT );
        if ($results) exit();
        
        $paystackTransDate = date('Y-m-d h:i:s', strtotime($event->data->paid_at));
        $table_name = $wpdb->prefix . 'transactions';
        $wpdb->insert($table_name, 
                array('ref_id' => $event->data->reference, 
                    'payment_code' =>  $event->data->metadata->custom_fields[1]->value,
                    'customer' =>  $event->data->metadata->custom_fields[0]->value,
                    'amount' =>  $event->data->amount,
                    'paystack_trans_date' =>  $paystackTransDate,
                    'source' =>  $event->data->channel
                    ), array( '%s', '%s', '%s', '%s', '%s', '%s'));
    }

    exit();
}

add_shortcode('iedc_payment_webhook_url', 'iedc_webHook');
?>
