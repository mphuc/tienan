<!DOCTYPE html>
<html>
<head>
  <title>Tiền ăn trong tuần</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
  <div class="container">
    <h2 class="text-center">
      <a href="index.php?route=account/account/prev_week&week=<?php echo intval($_GET['week']-1) ?>">
        <button type="button" class="btn">Prev</button>
      </a>
      <a href="index.php?route=account/account/next_week&week=<?php echo intval($_GET['week']+1) ?>">
        <button type="button" class="btn">Next</button>
      </a>
    </h2>       
    <div class="clearfix"></div>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th style="width: 60px;">Thứ</th>
          <?php $i=0; foreach ($get_all_username as $value) { $i++; ?>
             <th><?php echo $value['username'] ?></th> 
          <?php } ?>
          <th>Tiền ăn</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php for ($i=2; $i <9; $i++) { ?>
          <tr>
            
            <form method="POST" action="index.php?route=account/account/submit&week=<?php echo intval($_GET['week']) ?>&day=<?php echo $i ?>">
              <td>Thứ <?php echo $i ?></td>
              <td><input min="0" max="3" type="number" value="<?php echo $self -> get_data_number(1,intval($_GET['week']))['thu'.$i.''];?>" name="trung" class="form-control" /></td>
              <td><input min="0" max="3" type="number" value="<?php echo $self -> get_data_number(2,intval($_GET['week']))['thu'.$i.''];?>"  name="phong" class="form-control" /></td>
              <td><input min="0" max="3" type="number" value="<?php echo $self -> get_data_number(3,intval($_GET['week']))['thu'.$i.''];?>"  name="tai" class="form-control" /></td>
              <td><input min="0" max="3" type="number" value="<?php echo $self -> get_data_number(4,intval($_GET['week']))['thu'.$i.''];?>"  name="tu" class="form-control" /></td>
              <td><input min="0" max="3" type="number" value="<?php echo $self -> get_data_number(5,intval($_GET['week']))['thu'.$i.''];?>"  name="anh" class="form-control" /></td>
              <td><input min="0" max="3" type="number" value="<?php echo $self -> get_data_number(6,intval($_GET['week']))['thu'.$i.''];?>"  name="hien" class="form-control" /></td>
              <td><input min="0" max="3" type="number" value="<?php echo $self -> get_data_number(7,intval($_GET['week']))['thu'.$i.''];?>"  name="thuong" class="form-control" /></td>
              <td><input min="0" max="3" type="number" value="<?php echo $self -> get_data_number(8,intval($_GET['week']))['thu'.$i.''];?>"  name="huong" class="form-control" /></td>
              <td style="width: 210px">

                <?php 
                    $tienans = 0;
                    $customer_id_mua = 0;
                    foreach ($get_all_username as $valuesss) { 
                      $tienanss = $self -> get_data_number($valuesss['customer_id'],intval($_GET['week']));
                      if ($tienanss['amount_thu'.$i.''] > 0)
                      {
                        $tienans = $tienanss['amount_thu'.$i.''];
                        $customer_id_mua = $tienanss['customer_id'];
                      } 
                  }
                  ?>

                <input style="float: left; width: 90px;" type="number" value="<?php echo $tienans;?>" name="amout_total" class="form-control"  />
                <select style="float: left;width: 100px;" name="dicho" class="form-control">
                  <option value="0">--------</option>
                  <?php foreach ($get_all_username as $values) { ?>
                    <option 
                    <?php if ($values['customer_id'] == $customer_id_mua) echo 'selected="selected"' ?>
                     value="<?php echo $values['customer_id'] ?>"><?php echo $values['username'] ?></option>
                  <?php } ?>
                </select>
              </td>
              <td><button type="submit" class="btn btn-success">OK</button></td>
            </form>
          </tr>
        <?php } ?>
      </tbody>
  </table>
  <div class="clearfix"></div>

  <table class="table">
    <thead>
      <tr>
        <th>Tên</th>
        <th>T2</th>
        <th>T3</th>
        <th>T4</th>
        <th>T5</th>
        <th>T6</th>
        <th>T7</th>
        <th>T8</th>
        <th>Tổng tiền ăn</th>
        <th>Tổng chi</th>
        <th>Nhận về</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($get_all_username as $values) { ?>
        <tr>
          <td><?php echo $values['username'] ?></td>
          <td><?php echo $self -> calue_total($_GET['week'],$values['customer_id'],2); ?></td>
          <td><?php echo $self -> calue_total($_GET['week'],$values['customer_id'],3); ?></td>
          <td><?php echo $self -> calue_total($_GET['week'],$values['customer_id'],4); ?></td>
          <td><?php echo $self -> calue_total($_GET['week'],$values['customer_id'],5); ?></td>
          <td><?php echo $self -> calue_total($_GET['week'],$values['customer_id'],6); ?></td>
          <td><?php echo $self -> calue_total($_GET['week'],$values['customer_id'],7); ?></td>
          <td><?php echo $self -> calue_total($_GET['week'],$values['customer_id'],8); ?></td>
          <td>
            <?php echo $total_an = $self -> calue_total($_GET['week'],$values['customer_id'],2)+$self -> calue_total($_GET['week'],$values['customer_id'],3)+$self -> calue_total($_GET['week'],$values['customer_id'],4)+$self -> calue_total($_GET['week'],$values['customer_id'],5)+$self -> calue_total($_GET['week'],$values['customer_id'],6)+$self -> calue_total($_GET['week'],$values['customer_id'],7)+$self -> calue_total($_GET['week'],$values['customer_id'],8); ?>
          </td>

          <td><?php echo $total_chi = $self -> total_week($values['customer_id'],intval($_GET['week'])) ?></td>
          <td style="font-weight: bold; font-size: 18px"><?php echo $total_chi - $total_an ?></td>
        </tr>
      <?php } ?>
      
    </tbody>
  </table>

  </div>

<style type="text/css">
  a:focus, a:hover{
    text-decoration:none;
  }
  .form-control{
    text-align: center;
    font-weight: bold;
  }
  .container{
    width: 1200px;
    overflow-x: scroll;
  }
</style>
</body>
</html>