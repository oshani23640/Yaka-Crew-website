$( document ).ready(function() {

 document.getElementById("payment-form").addEventListener("submit", async (e) => {
  e.preventDefault();

  // Fetch client secret from your PHP backend
  const card = elements.getElement("card"); // ✅ get the card element object
  const response = await fetch("payment.php");
  const { clientSecret } = await response.json();
 const stripe = Stripe("pk_test_51Rz9cXAlz3HCjY3AhQpzZw6R1ClKN70jhIKqDhXLcNwzV6SqiQZ8Uf04zWl6DzUigacmJOZCLyjIUTm6p2aVQi1t00zJEks9Bn"); // your publishable key
  const result = await stripe.confirmCardPayment(clientSecret, {
    payment_method: {
      card: card   // ✅ pass the card element object here
    }
  });

  if (result.error) {
    alert(result.error.message);
  } else {
    if (result.paymentIntent.status === "succeeded") {
      alert("Payment successful!");
    }
  }
});


});