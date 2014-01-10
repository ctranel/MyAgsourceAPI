<div id="tab-container">
	<ul>
		<li><a href="events">Events</a></li>
		<li><a href="id">ID</a></li>
		<li><a href="dam">Dam</a></li>
		<li><a href="sire">Sire</a></li>
		<li><a href="tests">Tests</a></li>
		<li><a href="lactations">Lactations</a></li>
		<li><a href="graphs">Graphs</a></li>
	</ul>
	<div id="events">
		<h1>Cow Name=<?php echo $cow_name; ?></h1>
		<div class="withheld"><?php echo $withheld; ?></div>
	</div>
	<div id="id"></div>
	<div id="dam"></div>
	<div id="sire"></div>
	<div id="tests"></div>
	<div id="lactations"></div>
	<div id="graphs"></div>
	
	
	<div>
		<div class="withheld">-</div>
		<table border="0" cellpadding="3" cellspacing="0" width="93%">
			<tbody>
				<tr>
					<td class="content-left"><span id="item1" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Visible
							ID:<b> 159</b></span><br> <span id="item2" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Lact
							#:<b> 6</b></span><br> <span id="item3" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Preg
							Status:<b> Not Checked</b></span><br> <span id="item4"
						class="UserItem" title="Click to edit..."><img
							src="images/arrow-refresh-icon.png">Body Weight:<b> 0</b></span>
					</td>
					<td class="content-center"><span id="item5" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Days
							Last Bred:<b> </b></span><br> <span id="item6" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Due
							Date:<b> --</b></span><br> <span id="item7" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">SCC
							Count:<b> </b></span><br> <span id="item8" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Cntl
							#:<b> 1688</b></span></td>
					<td class="content-right"><span id="item9" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Visible
							ID:<b> 159</b></span><br> <span id="item10" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Due
							Date:<b> --</b></span><br> <span id="item11" class="UserItem"
						title="Click to edit..."><img src="images/arrow-refresh-icon.png">Days
							Since Last Preg Event:<b> </b></span><br> <span id="item12"
						class="UserItem" title="Click to edit..."><img
							src="images/arrow-refresh-icon.png">Current DIM:<b> 676</b></span>
					</td>
				</tr>
			</tbody>
		</table>
		<div>
			<div class="clear"></div>
			<table id="EVENTS_EVENTS" border="0" cellpadding="2" cellspacing="1"
				width="93%">
				<thead>
					<tr>
						<th align="CENTER" valign="MIDDLE" bgcolor="#CCCCCC"
							class="ui-state-default"><div class="DataTables_sort_wrapper">
								Edit <span class="css_right ui-icon ui-icon-carat-2-n-s"></span>
							</div></th>
						<th align="CENTER" valign="MIDDLE" bgcolor="#CCCCCC"
							class="ui-state-default"><div class="DataTables_sort_wrapper">
								Date <span class="css_right ui-icon ui-icon-carat-2-n-s"></span>
							</div></th>
						<th align="CENTER" valign="MIDDLE" bgcolor="#CCCCCC"
							class="ui-state-default"><div class="DataTables_sort_wrapper">
								Event <span class="css_right ui-icon ui-icon-carat-2-n-s"></span>
							</div></th>
						<th align="CENTER" valign="MIDDLE" bgcolor="#CCCCCC"
							class="ui-state-default"><div class="DataTables_sort_wrapper">
								<a
									style="float: right; background-color: #006666; color: #FFFFFF; FONT-SIZE: 16px; border: 2px outset #555"
									href="index.php?action=EVENTS&amp;bShow=ALL&amp;comp_num=1688&amp;token=686969169">
									Show All </a> Comment/Sire <span<div id="tabs"
										bgcolor="#CCCCCC" width="93%"
										class="ui-tabs ui-widget ui-widget-content ui-corner-all">
										<ul
											class="noPrint ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
											<li
												class="small-screen li-inline ui-state-default ui-corner-top"
												id="select_cow"><a href="#ui-tabs-1">Select Cow</a></li>
											<li id="events"
												class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a
												href="#ui-tabs-2">Events</a></li>
											<li id="events_id" class="ui-state-default ui-corner-top"><a
												href="#ui-tabs-3">ID</a></li>
											<li id="events_dam" class="ui-state-default ui-corner-top"><a
												id="dam" href="#ui-tabs-4">Dam</a></li>
											<li id="events_sire" class="ui-state-default ui-corner-top"><a
												href="#ui-tabs-5">Sire</a></li>
											<li id="events_test" class="ui-state-default ui-corner-top"><a
												href="#ui-tabs-6">Tests</a></li>
											<li id="events_lacts" class="ui-state-default ui-corner-top"><a
												href="#ui-tabs-7">Lactations</a></li>
											<li
												class="large-screen li-inline ui-state-default ui-corner-top"
												id="events_graphs"><a href="#ui-tabs-8">Graphs</a></li>
										</ul>
										<div id="ui-tabs-1"
											class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
										<div id="ui-tabs-2"
											class="ui-tabs-panel ui-widget-content ui-corner-bottom">
											<div id="cowheader">Cow Name=159</div>
											<div class="withheld">-</div>
											<table border="0" cellpadding="3" cellspacing="0" width="93%">
												<tbody>
													<tr>
														<td class="content-left"><span id="item1" class="UserItem"
															title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Visible ID:<b> 159</b></span><br>
															<span id="item2" class="UserItem"
															title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Lact #:<b> 6</b></span><br>
															<span id="item3" class="UserItem"
															title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Preg Status:<b> Not
																	Checked</b></span><br> <span id="item4"
															class="UserItem" title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Body Weight:<b> 0</b></span>
														</td>
														<td class="content-center"><span id="item5"
															class="UserItem" title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Days Last Bred:<b> </b></span><br>
															<span id="item6" class="UserItem"
															title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Due Date:<b> --</b></span><br>
															<span id="item7" class="UserItem"
															title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">SCC Count:<b> </b></span><br>
															<span id="item8" class="UserItem"
															title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Cntl #:<b> 1688</b></span>
														</td>
														<td class="content-right"><span id="item9"
															class="UserItem" title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Visible ID:<b> 159</b></span><br>
															<span id="item10" class="UserItem"
															title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Due Date:<b> --</b></span><br>
															<span id="item11" class="UserItem"
															title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Days Since Last Preg
																Event:<b> </b></span><br> <span id="item12"
															class="UserItem" title="Click to edit..."><img
																src="images/arrow-refresh-icon.png">Current DIM:<b> 676</b></span>


														</td>
													</tr>
												</tbody>
											</table>
											<br>
											<div class="dataTables_wrapper" id="EVENTS_EVENTS_wrapper">
												<div class="clear"></div>
												<table id="EVENTS_EVENTS" border="0" cellpadding="2"
													cellspacing="1" width="93%">
													<thead>
														<tr>
															<th align="CENTER" valign="MIDDLE" bgcolor="#CCCCCC"
																class="ui-state-default"><div
																	class="DataTables_sort_wrapper">
																	Edit <span
																		class="css_right ui-icon ui-icon-carat-2-n-s"></span>
																</div></th>
															<th align="CENTER" valign="MIDDLE" bgcolor="#CCCCCC"
																class="ui-state-default"><div
																	class="DataTables_sort_wrapper">
																	Date <span
																		class="css_right ui-icon ui-icon-carat-2-n-s"></span>
																</div></th>
															<th align="CENTER" valign="MIDDLE" bgcolor="#CCCCCC"
																class="ui-state-default"><div
																	class="DataTables_sort_wrapper">
																	Event <span
																		class="css_right ui-icon ui-icon-carat-2-n-s"></span>
																</div></th>
															<th align="CENTER" valign="MIDDLE" bgcolor="#CCCCCC"
																class="ui-state-default"><div
																	class="DataTables_sort_wrapper">
																	<a
																		style="float: right; background-color: #006666; color: #FFFFFF; FONT-SIZE: 16px; border: 2px outset #555"
																		href="index.php?action=EVENTS&amp;bShow=ALL&amp;comp_num=1688&amp;token=686969169">
																		Show All </a> Comment/Sire <span
																		class="css_right ui-icon ui-icon-carat-2-n-s"></span>
																</div></th>
														</tr>
													</thead>
													<tbody>
														<tr class="odd">
															<td align="CENTER" valign="MIDDLE" bgcolor="#FFFFFF"
																width="5%"><img src="images/delete-icon.png"
																class="submit_action" updel="DELETE_EVENT"
																intevent="1610495" evt="STATUS"></td>
															<td nowrap="" align="CENTER" valign="MIDDLE"
																bgcolor="#FFFFFF">03/19/2012</td>
															<td nowrap="" align="LEFT" valign="MIDDLE"
																bgcolor="#FFFFFF">Sold - Injury, Disease, Other</td>
															<td align="LEFT" valign="MIDDLE" bgcolor="#FFFFFF"></td>
														</tr>
														<tr class="even">
															<td align="CENTER" valign="MIDDLE" bgcolor="#FFFFFF"
																width="5%"></td>
															<td nowrap="" align="CENTER" valign="MIDDLE"
																bgcolor="#FFFFFF">03/05/2012</td>
															<td nowrap="" align="LEFT" valign="MIDDLE"
																bgcolor="#FFFFFF">Cow Calved</td>
															<td align="LEFT" valign="MIDDLE" bgcolor="#FFFFFF"></td>
														</tr>
													</tbody>
												</table>
											</div>
											<form action="index.php" name="event_form" id="event_form"
												method="POST">
												<input type="hidden" name="action" id="action_field"> <input
													type="hidden" name="intEvent" id="rec_num_field"> <input
													type="hidden" name="submitEvent" id="event_type">
											</form>

										</div>
										<div id="ui-tabs-3"
											class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
										<div id="ui-tabs-4"
											class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
										<div id="ui-tabs-5"
											class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
										<div id="ui-tabs-6"
											class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
										<div id="ui-tabs-7"
											class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
										<div id="ui-tabs-8"
											class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
									</div>class="css_right ui-icon ui-icon-carat-2-n-s"></span>
							</div></th>
					</tr>
				</thead>
				<tbody>
					<tr class="odd">
						<td align="CENTER" valign="MIDDLE" bgcolor="#FFFFFF" width="5%"><img
							src="images/delete-icon.png" class="submit_action"
							updel="DELETE_EVENT" intevent="1610495" evt="STATUS"></td>
						<td nowrap="" align="CENTER" valign="MIDDLE" bgcolor="#FFFFFF">
							03/19/2012</td>
						<td nowrap="" align="LEFT" valign="MIDDLE" bgcolor="#FFFFFF">Sold
							- Injury, Disease, Other</td>
						<td align="LEFT" valign="MIDDLE" bgcolor="#FFFFFF"></td>
					</tr>
					<tr class="even">
						<td align="CENTER" valign="MIDDLE" bgcolor="#FFFFFF" width="5%"></td>
						<td nowrap="" align="CENTER" valign="MIDDLE" bgcolor="#FFFFFF">
							03/05/2012</td>
						<td nowrap="" align="LEFT" valign="MIDDLE" bgcolor="#FFFFFF">Cow
							Calved</td>
						<td align="LEFT" valign="MIDDLE" bgcolor="#FFFFFF"></td>
					</tr>
				</tbody>
			</table>
		</div>
		<form action="index.php" name="event_form" id="event_form"
			method="POST">
			<input type="hidden" name="action" id="action_field"> <input
				type="hidden" name="intEvent" id="rec_num_field"> <input
				type="hidden" name="submitEvent" id="event_type">
		</form>

	</div>
	<div id="ui-tabs-3"
		class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
	<div id="ui-tabs-4"
		class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
	<div id="ui-tabs-5"
		class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
	<div id="ui-tabs-6"
		class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
	<div id="ui-tabs-7"
		class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
	<div id="ui-tabs-8"
		class="ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide"></div>
</div>