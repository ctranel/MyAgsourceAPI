    <?php //var_dump($tests); ?>
    Expand test date to see reports.
    <div class="css-treeview">
		<ul><?php foreach($tests as $k=>$t){ ?>
			<li><input type="checkbox" id="item-<?php echo $k ?>" /><label for="item-<?php echo $k ?>"><?php echo $t['test_date']; ?></label>
                <ul><?php foreach($t['reports'] as $r){ ?>
                        <li><a href="dhi/pdf_archive/show/<?php echo $r['id']; ?>"><?php echo $r['text']; ?></a></li>
                <?php } ?></ul>
            </li>
        <?php } ?></ul>
	</div>
