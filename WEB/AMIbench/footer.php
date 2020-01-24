<?php
print <<< HEADER
 <footer class="row-footer">
        <div class="container-fluid">
            <div class="row">
                    <ul class="list-unstyled">
                        <li><a href="/AMIbench/index.php">Home</a></li>
                    </ul>
                </div>
                <div class="col-xs-3">
                <form>
                <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search">
                <div class="input-group-btn">
                         <button class="btn btn-default" type="submit">
                                <i class="glyphicon glyphicon-search"></i>
                        </button>
                </div>
            </div>
          </div>
        <div class="container-fluid">
         <div class="row">
          <div class="col-xs-12">
                    <p align=center>© Netflix Inc.</p>
            </div>
          </div>
        </div>
 </footer>
<script>
function validate()
        {
            var selectChoose = document.getElementById('choose');
            var maxOptions = 2;
            var optionCount = 0;
            for (var i = 0; i < selectChoose.length; i++) {
                if (selectChoose[i].selected) {
                    optionCount++;
                    if (optionCount > maxOptions) {
                        alert("Please select two instances only ")
                        return false;
                    }
                }
            }
            return true;
        }
</script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
HEADER;
?>
