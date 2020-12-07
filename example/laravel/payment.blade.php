<!-- // Fichier ressources/views/adjeminpay/payement.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payer Via AdjeminPay</title>
</head>

<body>
    <script src="https://www.cdn.adjeminpay.net/release/seamless/latest/adjeminpay.min.js" type="text/javascript">
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>

    <div id="result">
        <h1 id="result-title"></h1>
        <p id="result-message"></p>
        <p id="result-status"></p>
    </div>

    <input type="text" id="amount" value="{{$transaction->amount}}">

    <input type="text" id="currency" value="CFA">

    <!-- La longeur maximum d'un id de transaction est de 191 caractères -->
    <input type="text" id="transaction_id"
        value="{{$transaction->reference}}">

    <input type="text" id="custom" value="{{$custom}}">

    <input type="text" id="designation" value="{{$transaction->designation}}">

    <button id="payBtn">Payer</button>

    <script>
        var AdjeminPay = AdjeminPay();

        AdjeminPay.on('init', function (e) {
            // retourne une erreur au cas où votre API_KEY ou APPLICATION_ID est incorrecte
            console.log(e);

        });

        // Lance une requete ajax pour vérifier votre API_KEY et APPLICATION_ID
        AdjeminPay.init({
            apikey: "{{$apikey}}",
            application_id: "{{$application_id}}",
        });

        // Ecoute le feedback sur les erreurs
        AdjeminPay.on('error', function (e) {
            // la fonction que vous définirez ici sera exécutée en cas d'erreur
            console.log(e);
            $("#result-title").html(e.title);
            $("#result-message").html(e.message);
            $("#result-status").html(e.status);
        });

        // Lancer la procédure de paiement au click
        $('#payBtn').on('click', function () {

            // Vérifie vos informations et prépare le paiement
            // S'il y a une erreur à cette étape, AdjeminPay.on('error')
            // sera exécutée

            AdjeminPay.preparePayment({
                amount: parseInt($('#amount').val()),
                transaction_id: $('#transaction_id').val(),
                currency: $('#currency').val(),
                designation: $('#designation').val(),
                custom: $('#custom').val(),
                notify_url: "{{route('api.transaction.notify')}}"

            });

            // Si l'étape précédante n'a pas d'erreur,
            // cette ligne génère et affiche l'interface de paiement AdjeminPay
            AdjeminPay.renderPaymentView();
        });

        // Payment terminé
        AdjeminPay.on('paymentTerminated', function (e) {
            console.log('<<<<<<< terminated !');
            console.log('>>>>>>> Paiement terminé !');

            $("#result-title").html(e.title);
            $("#result-message").html(e.message);
            $("#result-status").html(e.status);
            // Action
        });
        // Payment réussi
        AdjeminPay.on('paymentSuccessful', function (e) {
            console.log('<<<<<<< Successful !');
            console.log('>>>>>>> Paiement réussi !');

            $("#result-title").html(e.title);
            $("#result-message").html(e.message);
            $("#result-status").html(e.status);
        });
        // Payment échoué
        AdjeminPay.on('paymentFailed', function (e) {
            console.log('<<<<<<< Echec !');
            console.log('>>>>>>> Paiement echoué !');

            $("#result-title").html(e.title);
            $("#result-message").html(e.message);
            $("#result-status").html(e.status);
        });

        // Payment annulé
        AdjeminPay.on('paymentCancelled', function (e) {
            console.log('<<<<<<< Echec !');
            console.log('>>>>>>> Paiement annulé !');

            $("#result-title").html(e.title);
            $("#result-message").html(e.message);
            $("#result-status").html(e.status);
        });
    </script>

</body>

</html>
