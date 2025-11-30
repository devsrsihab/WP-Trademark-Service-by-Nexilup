(function ($) {
  console.log("delete button js");

  // Delete btn
  const deleteBtns = document.querySelectorAll(".tm-delete-price");
  console.log("log", deleteBtns);

  if (!deleteBtns) return;
  deleteBtns.addEventListener("click", function (e) {
    e.preventDefault();
    if (!confirm("Are you sure you want to delete this pricing?")) {
      e.preventDefault(); // STOP delete
    }
  });
})(jQuery);
