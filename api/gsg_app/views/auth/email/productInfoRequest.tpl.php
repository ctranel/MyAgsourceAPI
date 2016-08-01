<?php echo $name; ?> (Herd <?php echo $herd_code; ?>, <?php echo $email; ?>) has requested information on the following products:
<ul>
    <li>
        <?php echo implode('</li><li>', $products); ?>
    </li>
</ul>
<?php if(!empty($comments)){ ?>
    <?php echo $name; ?> also said: <?php echo $comments; ?>
<?php }
