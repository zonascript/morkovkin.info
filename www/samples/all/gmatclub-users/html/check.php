<?php

sleep(1);

$coupon = $_POST['coupon'];

if ($coupon === 'BIG-DISCO')
{
    echo json_encode(array('value' => 99, 'percent' => 50));
}
else
{
    echo json_encode(false);
}

?>