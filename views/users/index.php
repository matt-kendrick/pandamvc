<h2>Users</h2>

<div id="alertUserSuccess" class="alert alert-success" role="alert" style="display:none;"></div>

<table id="tblUsers" class="table">
	<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal" data-id="0">New User</button>

<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="userModalLabel">User</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			<div id="alertUserDanger" class="alert alert-danger" role="alert" style="display:none;"></div>
				<form id="frmUser">
					<input type="hidden" id="userId">
					<div class="form-group">
						<label class="form-check-label" for="name">Name</label>
						<input type="text" name="name" id="userName" autofocus>
					</div>
					<div class="form-group">
						<label class="form-check-label" for="email">Email Address</label>
						<input type="text" name="email" id="userEmail">
					</div>
					<p><a href="#" id="btnChangePassword">Change Password</a></p>
					<div id="passwordInfo" style="display:none;">
						<p><strong>Password</strong></p>
						<div class="form-group user-update-only" style="display:none;">
							<label class="form-check-label" for="currentPassword">Current Password</label>
							<input type="password" name="currentPassword" id="currentPassword">
						</div>
						<div class="form-group">
							<label class="form-check-label" for="password">Password</label>
							<input type="password" name="password" id="password">
						</div>
						<div class="form-group">
							<label class="form-check-label" for="passwordConfirm">Password Confirm</label>
							<input type="password" name="passwordConfirm" id="passwordConfirm">
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
			
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			<button type="button" id="btnUserSubmit" class="btn btn-primary">Submit</button>
			</div>
		</div>
	</div>
</div>	

<script type="text/javascript">
$(document).ready(function(){
	
	//load the table
	function populateUsersTable()
	{
		$.ajax
		({
		  type: "GET",
		  url: "/users/getUsersJson/",
		  dataType: "json",
		  success: function(users)
		  {
			users.forEach(function(user)
			{
				$('#tblUsers tbody').append("<tr><td>"+user.name+"</td><td>"+user.email+"</td><td><a data-toggle='modal' href='#userModal' data-id='"+user.id+"'>Edit</a> <a href='#' class='btnUserDelete' data-id='"+user.id+"' >Delete</a></td></tr>");
			});
		  },
		  error: function(){
			  $("#alertUserDanger").show();
			  $("#alertUserDanger").html("<strong>Warning!</strong> Failed to load users.");
		  }
		});
	}
	
	//clear form
	function clearUserForm()
	{
		$("#alertUserDanger").hide();
		$("#alertUserDanger").html("");
	  
		$("#userId").val(""); 
		$("#userName").val("");
		$("#userEmail").val("");
		
		$("#password").val("");
		$("#passwordConfirm").val("");
		
		$("#passwordInfo").hide();
	}

	//process modal popup
	$("#userModal").on("show.bs.modal", function (event) {
	  var button = $(event.relatedTarget) // Button that triggered the modal
	  var id = button.data('id') // Extract info from data-* attributes

	  clearUserForm();
	  
		$.ajax
		({
		  type: "GET",
		  url: "/users/getUserJson/"+id,
		  dataType: "json",
		  success: function(user)
		  {
			$("#userId").val(user.id); 
			$("#userName").val(user.name);
			$("#userEmail").val(user.email);
			

		  },
		  error: function(response){
			console.log(response);
			$("#alertUserDanger").show();
			$("#alertUserDanger").html("<strong>Warning!</strong> Failed to load user.");
		  }
		});
	  
	  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
	  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
	  var modal = $(this)
	  modal.find(".modal-title").html((id == 0 ? "Create":"Update") + " User");
	  
	  if(id == 0) $("#btnChangePassword").hide();
	  else $("#btnChangePassword").show();
	  
	  //have to wait until the modal is finished fading in
	  setTimeout(function (){$("#userName").focus();}, 1000);
	})
	
	$("#btnChangePassword").click(function(){
		
		$("#password").val("");
		$("#passwordConfirm").val("");
		
		$("#passwordInfo").toggle();
	});
	
	//process submit
	$("#btnUserSubmit").on("click",function(){
		
		//reset alert
		$("#alertUserDanger").hide();
		$("#alertUserDanger").html("");
		
		var user = {id:$("#userId").val(), name:$("#userName").val(), email:$("#userEmail").val()};
		
		//check for password changes
		if($("#password").val().length > 0)
		{
			//if confirmation matches
			if($("#password").val() == $("#passwordConfirm").val()) 
			{
				user.password = $("#password").val();
			}
			else
			{
				$("#alertUserDanger").show();
				$("#alertUserDanger").html("<strong>Warning!</strong> Password and Password confirmation do not match.");
				return;
			}
		}

		$.ajax
		({
			type: "POST",
			url: "/users/process",
			data: user,
			dataType: "json",
			success: function(json)
			{
				$("#tblUsers tbody").empty();
				populateUsersTable();
				$("#userModal").modal("toggle");
				
				if(user.id == 0) $("#alertUserSuccess").html("<strong>Success!</strong> Added: " + json.name);
				else $("#alertUserSuccess").html("<strong>Success!</strong> Updated: " + json.name);
				
				clearUserForm();
				
				$("#alertUserSuccess").show().delay(5000).fadeOut();
			},
			error: function(response)
			{
				console.log(response);
				$("#alertUserDanger").show();
				$("#alertUserDanger").html("<strong>Warning!</strong> Failed to submit.");
			}
		});
	});
	
	//process delete
	$(document).on('click', '.btnUserDelete', function()
	{
		var user = {id:$(this).attr("data-id")};
		
		bootbox.confirm("Are you sure you want to delete this user?", function(result){
			
			if(result)
			{
				$.ajax
				({
					type: "POST",
					url: "/users/delete",
					data: user,
					dataType: "json",
					success: function(json)
					{
						$("#tblUsers tbody").empty();
						populateUsersTable();
						
						clearUserForm();
						
						$("#alertUserSuccess").html("<strong>Success!</strong> " + json.message);
						$("#alertUserSuccess").show().delay(5000).fadeOut();
					},
					error: function(response)
					{
						console.log(response);
						
						$("#alertUserDanger").html("<strong>Warning!</strong> Failed to submit.");
						$("#alertUserDanger").show();
					}
				});
			}
		});
	});

	//onload
	populateUsersTable();
});	
</script>