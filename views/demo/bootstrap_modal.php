<!-- Button trigger modal -->
<a data-toggle="modal" href="http://getbootstrap.com/javascript/#modals" data-target="#myModal">Click me</a>
<!--
<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
	Launch demo modal
</button>-->

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Modal title</h4>
			</div>
			<div class="modal-body">
				<div>
					<img src="./test.jpg" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>

//	$('#myModal').modal('toggle');
//	$('#myModal').modal('show');
//	$('#myModal').modal('hide');

	$('#myModal').modal({
		backdrop: 'static', // boolean or the string 'static'
		keyboard: true, //boolean
		show: false, //boolean
		remote: true
	});

	$('#myModal').on('show.bs.modal', function(e) {
		console.log('show.bs.modal');
	});

	$('#myModal').on('shown.bs.modal', function(e) {
		console.log('shown.bs.modal');
	});

	$('#myModal').on('hide.bs.modal', function(e) {
		console.log('hide.bs.modal');
	});

	$('#myModal').on('hidden.bs.modal', function(e) {
		console.log('hidden.bs.modal');
	});
</script>