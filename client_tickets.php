<?php 

//Paging
if(isset($_GET['p'])){
  $p = intval($_GET['p']);
  $record_from = (($p)-1)*$_SESSION['records_per_page'];
  $record_to = $_SESSION['records_per_page'];
}else{
  $record_from = 0;
  $record_to = $_SESSION['records_per_page'];
  $p = 1;
}
  
if(isset($_GET['q'])){
  $q = mysqli_real_escape_string($mysqli,$_GET['q']);
}else{
  $q = "";
}

if(!empty($_GET['sb'])){
  $sb = mysqli_real_escape_string($mysqli,$_GET['sb']);
}else{
  $sb = "ticket_number";
}

if(isset($_GET['o'])){
  if($_GET['o'] == 'ASC'){
    $o = "ASC";
    $disp = "DESC";
  }else{
    $o = "DESC";
    $disp = "ASC";
  }
}else{
  $o = "DESC";
  $disp = "ASC";
}

//Rebuild URL
$url_query_strings_sb = http_build_query(array_merge($_GET,array('sb' => $sb, 'o' => $o)));

$sql = mysqli_query($mysqli,"SELECT SQL_CALC_FOUND_ROWS * FROM tickets 
  LEFT JOIN contacts ON ticket_contact_id = contact_id
  LEFT JOIN users ON ticket_assigned_to = user_id
  LEFT JOIN assets ON ticket_asset_id = asset_id
  LEFT JOIN locations ON ticket_location_id = location_id
  WHERE ticket_client_id = $client_id
  AND (CONCAT(ticket_prefix,ticket_number) LIKE '%$q%' OR ticket_subject LIKE '%$q%' OR ticket_status LIKE '%$q%' OR ticket_priority LIKE '%$q%' OR user_name LIKE '%$q%')
  ORDER BY $sb $o LIMIT $record_from, $record_to");

$num_rows = mysqli_fetch_row(mysqli_query($mysqli,"SELECT FOUND_ROWS()"));

?>

<div class="card card-dark">
  <div class="card-header py-2">
    <h3 class="card-title mt-2"><i class="fa fa-fw fa-ticket-alt"></i> Tickets</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTicketModal"><i class="fas fa-fw fa-plus"></i> New Ticket</button>
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addScheduledTicketModal"><i class="fas fa-fw fa-plus"></i> Scheduled Ticket</button>
    </div>
  </div>
  <div class="card-body">
    <form autocomplete="off">
      <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
      <input type="hidden" name="tab" value="<?php echo strip_tags($_GET['tab']); ?>">
      <div class="row">
        
        <div class="col-md-4">
          <div class="input-group mb-3 mb-md-0">
            <input type="search" class="form-control" name="q" value="<?php if(isset($q)){echo stripslashes($q);} ?>" placeholder="Search <?php echo ucwords(strip_tags($_GET['tab'])); ?>">
            <div class="input-group-append">
              <button class="btn btn-dark"><i class="fa fa-search"></i></button>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="float-right">
            <a href="post.php?export_client_<?php echo strip_tags($_GET['tab']); ?>_csv=<?php echo $client_id; ?>" class="btn btn-default"><i class="fa fa-fw fa-download"></i> Export</a>
          </div>
        </div>

      </div>
    </form>
    <hr>
    <div class="table-responsive">
      <table class="table table-striped table-borderless table-hover">
        <thead class="text-dark <?php if($num_rows[0] == 0){ echo "d-none"; } ?>">
          <tr>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=ticket_number&o=<?php echo $disp; ?>">Number</a></th> 
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=ticket_subject&o=<?php echo $disp; ?>">Subject</a></th> 
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=contact_name&o=<?php echo $disp; ?>">Contact</a></th>          
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=ticket_priority&o=<?php echo $disp; ?>">Priority</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=ticket_status&o=<?php echo $disp; ?>">Status</a>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=user_name&o=<?php echo $disp; ?>">Assigned</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=ticket_updated_at&o=<?php echo $disp; ?>">Last Response</a></th>
            <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=ticket_created_at&o=<?php echo $disp; ?>">Created</a></th>
            
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
      
          while($row = mysqli_fetch_array($sql)){
            $ticket_id = $row['ticket_id'];
            $ticket_prefix = $row['ticket_prefix'];
            $ticket_number = $row['ticket_number'];
            $ticket_subject = $row['ticket_subject'];
            $ticket_details = $row['ticket_details'];
            $ticket_priority = $row['ticket_priority'];
            $ticket_status = $row['ticket_status'];
            $ticket_created_at = $row['ticket_created_at'];
            $ticket_updated_at = $row['ticket_updated_at'];
              if (empty($ticket_updated_at)) {
                  if($ticket_status == "Closed"){
                      $ticket_updated_at_display = "<p>Never</p>";
                  }
                  else{
                      $ticket_updated_at_display = "<p class='text-danger'>Never</p>";
                  }
              } else {
                  $ticket_updated_at_display = $ticket_updated_at;
              }
            $ticket_closed_at = $row['ticket_closed_at'];
            
            if($ticket_status == "Open"){
              $ticket_status_display = "<span class='p-2 badge badge-primary'>$ticket_status</span>";
            }elseif($ticket_status == "Working"){
              $ticket_status_display = "<span class='p-2 badge badge-success'>$ticket_status</span>";
            }else{
              $ticket_status_display = "<span class='p-2 badge badge-secondary'>$ticket_status</span>";
            }

            if($ticket_priority == "High"){
              $ticket_priority_display = "<span class='p-2 badge badge-danger'>$ticket_priority</span>";
            }elseif($ticket_priority == "Medium"){
              $ticket_priority_display = "<span class='p-2 badge badge-warning'>$ticket_priority</span>";
            }elseif($ticket_priority == "Low"){
              $ticket_priority_display = "<span class='p-2 badge badge-info'>$ticket_priority</span>";
            }else{
              $ticket_priority_display = "-";
            }
              $ticket_assigned_to = $row['ticket_assigned_to'];
              if (empty($ticket_assigned_to)) {
                  if($ticket_status == "Closed"){
                      $ticket_assigned_to_display = "<p>Not Assigned</p>";
                  }
                  else{
                      $ticket_assigned_to_display = "<p class='text-danger'>Not Assigned</p>";
                  }
              } else {
                  $ticket_assigned_to_display = $row['user_name'];
              }
            $contact_id = $row['contact_id'];
            $contact_name = $row['contact_name'];
            if(empty($contact_name)){
              $contact_display = "-";
            }else{
              $contact_display = "$contact_name<br><small class='text-secondary'>$contact_email</small>";
            }
            $contact_title = $row['contact_title'];
            $contact_email = $row['contact_email'];
            $contact_phone = $row['contact_phone'];
            if(strlen($contact_phone)>2){ 
              $contact_phone = substr($row['contact_phone'],0,3)."-".substr($row['contact_phone'],3,3)."-".substr($row['contact_phone'],6,4);
            }
            $contact_extension = $row['contact_extension'];
            $contact_mobile = $row['contact_mobile'];
            if(strlen($contact_mobile)>2){ 
              $contact_mobile = substr($row['contact_mobile'],0,3)."-".substr($row['contact_mobile'],3,3)."-".substr($row['contact_mobile'],6,4);
            }

          ?>

          <tr>
            <td><a href="ticket.php?ticket_id=<?php echo $ticket_id; ?>"><span class="badge badge-pill badge-secondary p-3"><?php echo "$ticket_prefix$ticket_number"; ?></span></a></td>
            <td><a href="ticket.php?ticket_id=<?php echo $ticket_id; ?>"><?php echo $ticket_subject; ?></a></td>
            <td><?php echo $contact_display; ?></td>
            <td><?php echo $ticket_priority_display; ?></td>
            <td><?php echo $ticket_status_display; ?></td>                   
            <td><?php echo $ticket_assigned_to_display; ?></td>
            <td><?php echo $ticket_updated_at_display; ?></td>
            <td><?php echo $ticket_created_at; ?></td>
            <td>
              <?php if($ticket_status !== "Closed") { ?>
                <div class="dropdown dropleft text-center">
                  <button class="btn btn-secondary btn-sm" type="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-h"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editTicketModal<?php echo $ticket_id; ?>">Edit</a>
                    <?php if($session_user_role == 3) { ?>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item text-danger" href="post.php?delete_ticket=<?php echo $ticket_id; ?>">Delete</a>
                    <?php } ?>
                  </div>
                </div>
              <?php } ?>
            </td>
          </tr>

          <?php

          include("ticket_edit_modal.php");
          }

          ?>

        </tbody>
      </table>
    </div>
    <?php include("pagination.php"); ?>
  </div>
</div>

<?php
include("ticket_add_modal.php");
include("scheduled_ticket_add_modal.php");
?>
