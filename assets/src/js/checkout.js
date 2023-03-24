jQuery(function($) {
  $('[id=flw-pay-now-button]').each( function(index) {
      $(this).on('click', function (evt) {
          evt.preventDefault();
          const {log} = console;
          let payment_made = false;
          let cancelUrl = window.location.href;

          const { payment_style, cb_url, currency } = flw_payment_args;
          const redirectPost = function (location, args) {
              let form = "";
              $.each(args, function (key, value) {
                  // value = value.split('"').join('\"')
                  form += '<input type="hidden" name="' + key + '" value="' + value + '">';
              });
              $('<form action="' + location + '" method="POST">' + form + "</form>")
                  .appendTo($(document.body))
                  .submit();
          };
          
          const processData = () => {
              const cbUrl = flw_payment_args.cb_url;
              let payload = {
                  amount: flw_payment_args.amount,
                  country: flw_payment_args.country,
                  currency: flw_payment_args.currency,
                  custom_description: flw_payment_args.desc,
                  customer_email: flw_payment_args.email,
                  customer_firstname: flw_payment_args.firstname,
                  customer_lastname: flw_payment_args.lastname,
                  txref: flw_payment_args.txnref,
                  payment_options: flw_payment_args.payment_options,
                  PBFPubKey: flw_payment_args.p_key,
                  onclose: function () {
                      if (payment_made) {
                          $.blockUI({message: '<p> confirming transaction ...</p>'});
                          redirectPost(cbUrl + "?txref=" + tr, flw_payment_args.txnref);
                      }
                  },
                  callback: function (response) {
                      let tr =
                          response.data.data?.txRef || response.data.transactionobject?.txRef;
                      if (
                          response.tx.chargeResponseCode == "00" ||
                          response.tx.chargeResponseCode == "0"
                      ) {
                          payment_made = true;
                          // popup.close();
                          $.blockUI({message: '<p> confirming transaction ...</p>'});
                          redirectPost(cbUrl + "?txref=" + tr, response.tx);
                      } else {
                          alert(response.respmsg);
                      }

                      popup.close(); // close modal
                  },
              };
              let popup = window.getpaidSetup(payload);
          }

          if (payment_style === "inline") {
              processData();
          } else {
              location.href = cb_url;
          }
      });
  });
});