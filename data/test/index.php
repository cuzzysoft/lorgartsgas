<html>

<head>
<?php include('../headlinks.php'); ?>
<style>
    .hidden {
  display: none;
}

</style>
</head>
<body>
<div class="container">
  <div class="card">
    <div class="card-body">
      <div class="text-center">
        <button type="button" class="btn mb-1 btn-primary" id="load-dt">Click to load DataTable</button>
      </div>
      <div class="table-responsive hidden">
        <table id="animals-table" class="table table-striped table-bordered" style="width: 100%">
          <thead>
            <tr>
              <td>Id</td>
              <td>GUID</td>
              <td>Name</td>
              <td>Status</td>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
 $(document).ready(function () {

  $("#load-dt").click(function () {
      alert("ok");
    if ($(".table-responsive").hasClass("hidden")) {
      $(".table-responsive").removeClass("hidden");
      $.ajax({
        url: "https://api.srv3r.com/table/",
        type: "GET"
      }).done(function (result) {
        animal_table.clear().draw();
        animal_table.rows.add(result).draw();
      });
    }
  });
});

let animal_table = $("#animals-table").DataTable({
  pageLength: 20,
  lengthMenu: [20, 30, 50, 75, 100],
  order: [],
  paging: true,
  searching: true,
  info: true,
  data: [],
  columns: [
    { data: "id" },
    { data: "guid" },
    { data: "name" },
    { data: "status" }
  ]
});

</script>
</body>
</html>