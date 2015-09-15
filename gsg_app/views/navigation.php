			<!--top navigation-->
			<nav class="navbar navbar-inverse" id="top-nav" role="navigation">
				<ul class="nav navbar-nav">
					<li><a>Data Entry</a></li>
					<!-- ko foreach: children -->
						<li data-bind="css:{'current': (isSelected)}"><a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a></li>
					<!-- /ko -->
					<li><a>Cow Lookup</a></li>
					<li><a>Semen Inventory</a></li>
				</ul>
			</nav>
			<!-- ko if: (typeof(selectedChild()) !== 'undefined') -->
				<!-- ko if: (selectedChild().threeLevelNav) -->
				<nav class="navbar layer" id="top-nav2-layer" role="navigation" data-bind="with: selectedChild">
					<ul class="nav navbar-nav" data-bind="foreach: children">
						<li data-bind="css:{'current': (isSelected)}"><a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a></li>
					</ul>
				</nav>
				<nav class="navbar mega" id="top-nav2-mega" role="navigation" data-bind="with: selectedChild().selectedChild">
					<div class="nav navbar-nav">
						<nav class="navbar mega-category" id="top-nav3-mega" role="navigation">
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
				<nav class="navbar layer" id="top-nav2-layer" role="navigation" data-bind="with: selectedChild">
					<ul class="nav navbar-nav" data-bind="foreach: children">
						<li data-bind="css:{'current': (isSelected)}"><a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a></li>
					</ul>
				</nav>
				<nav class="navbar mega" id="top-nav2-mega" role="navigation" data-bind="with: selectedChild().selectedChild">
				<div class="nav navbar-nav">
						<ul data-bind="foreach: children">
							<li>
								<a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a>
							</li>
						</ul>
					</div>
				</nav>
				<!-- /ko -->
				<!-- ko if: (selectedChild().twoLevelMega) -->
				<nav class="navbar mega-category" id="top-nav2-mega" role="navigation">
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
				<nav class="navbar mega" id="top-nav2-mega" role="navigation" data-bind="foreach: selectedChild().children">
					<div class="nav navbar-nav">
						<nav class="navbar" id="top-nav3" role="navigation">
							<a data-bind="text: name"></a>
							<ul data-bind="foreach: children">
								<li data-bind="css:{'heading': (children().length > 0)}">
									<a data-bind="click: $parent.setSelected, text: name, attr: {href: href}"></a>
								</li>
							</ul>
						</nav>
					</div>
				</nav>
				<!-- /ko -->
			<!-- /ko -->
