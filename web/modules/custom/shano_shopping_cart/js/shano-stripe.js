document.getElementById('stripe-button').addEventListener('click', function(){
  const CHECKOUT_SESSION_ID = drupalSettings.stripe_session_id;
  const STRIPE_PUBLIC_KEY = drupalSettings.stripe_public_key;
  let stripe = Stripe(STRIPE_PUBLIC_KEY);

  stripe.redirectToCheckout({
    sessionId: CHECKOUT_SESSION_ID
  }).then(function (result) {
    alert(result.error.message);
  });

});
