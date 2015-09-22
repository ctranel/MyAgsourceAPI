			<!--top navigation-->
			<nav class="navbar navbar-inverse" id="top-nav" role="navigation">
				<ul class="nav navbar-nav" style="display:none"  data-bind="visible: numChildren() > 0">
					<!-- ko foreach: children -->
						<li data-bind="css:{'current': (isSelected)}, attr:{id: id}"><a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a></li>
					<!-- /ko -->
					<li style="width: 100%"><a name="top-nav">&nbsp</a></li>
				</ul>
			</nav>
			<!-- ko if: (typeof(selectedChild()) !== 'undefined') -->
				<!-- ko if: (selectedChild().threeLevelNav) -->
				<nav class="navbar layer" id="top-nav2-layer" style="display:none" role="navigation" data-bind="visible: selectedChild().threeLevelNav, with: selectedChild">
					<ul class="nav navbar-nav" data-bind="foreach: children">
						<!-- ko foreach: children -->
							<li data-bind="css:{'current': (isSelected)}, attr:{id: id}"><a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a></li>
						<!-- /ko -->
						<li style="width: 100%"><a name="top-nav">&nbsp</a></li>
					</ul>
				</nav>
				<nav class="navbar mega" id="top-nav2-mega" role="navigation" data-bind="visible: $parent.isSelected(), with: selectedChild().selectedChild">
					<div class="nav navbar-nav">
						<nav class="navbar mega-category" id="top-nav3-mega" style="display:none" role="navigation" data-bind="visible: $parent.isSelected()">
							<ul data-bind="foreach: children">
								<li data-bind="css:{'heading': (children().length > 0)}">
									<a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a>
									<ul data-bind="foreach: children">
										<li>
											<a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a>
										</li>
									</ul>
								</li>
							</ul>
						</nav>
					</div>
				</nav>
				<!-- /ko -->
				<!-- ko if: (selectedChild().twoLevelNavWithBar) -->
				<nav class="navbar layer" id="top-nav2-layer" style="display:none" role="navigation" data-bind="visible: selectedChild().twoLevelNavWithBar, with: selectedChild">
					<ul class="nav navbar-nav" data-bind="foreach: children">
						<li data-bind="css:{'current': (isSelected)}, attr:{id: id}">
							<a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a>
							<!-- ko if: (isSelected()) -->
							<nav class="navbar mega" id="top-nav2-mega" role="navigation">
							<div class="nav navbar-nav">
									<ul data-bind="foreach: children">
										<li>
											<a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a>
										</li>
									</ul>
								</div>
							</nav>
							<!-- /ko -->
						</li>
					</ul>
				</nav>
				<!-- /ko -->
				<!-- ko if: (selectedChild().twoLevelMega) -->
				<nav class="navbar mega-category" id="top-nav2-mega" style="display:none" role="navigation" data-bind="visible: selectedChild().twoLevelMega()">
					<div class="nav navbar-nav">
						<ul data-bind="foreach: selectedChild().children">
						<li>
						<nav class="navbar mega" id="top-nav3-mega" role="navigation">
							<a data-bind="text: name"></a>
							<ul data-bind="foreach: children">
								<li>
									<a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a>
								</li>
							</ul>
						</nav>
						</li>
						</ul>
					</div>
				</nav>
				<!-- /ko -->
				<!-- ko if: (selectedChild().oneLevel) -->
				<nav class="navbar mega" id="top-nav2-mega" style="display:none" role="navigation" data-bind="visible: selectedChild().oneLevel()">
					<div class="nav navbar-nav" data-bind="tedxt: selectedChild().children().length">
						<ul data-bind="foreach: selectedChild().children">
							<li>
								<a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a>
							</li>
						</ul>
					</div>
				</nav>
				<!-- /ko -->
			<!-- /ko -->
