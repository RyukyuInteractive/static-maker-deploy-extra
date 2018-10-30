document.addEventListener('DOMContentLoaded', function () {
	window.state = {
		deployType: 'whole',
		partialConfirm: false,
		showDiff: false,
		diffData: [],
		checkedFiles: {},
	};

	var setState = function (newState) {
		// merge state
		state = Object.keys(newState).reduce(function (a, c) {
			a[c] = newState[c];
			return a;
		}, state);

		applyState();
	};

	/**
	 * called every time the state is updated
	 */
	var applyState = function () {
		console.log(state);

		// render components
		document.querySelector('.smde-deploy-form-wrapper').appendChild(DeployScheduleButtonComponent(state).create());

		document.querySelector('.diff-actions').appendChild(DiffActionsComponent(state).create());
		document.querySelector('.diff-table-output').appendChild(DiffTableComponent(state).create());
		document.querySelector('.diff-confirm-output').appendChild(ConfirmListComponent(state).create());

		document.querySelector('.deploy-type-buttons').appendChild(DeployTypeRadioComponent(state).create());
	};

	var removeAllExsistingDoms = function (className) {
		Array.prototype.forEach.call(document.querySelectorAll('.' + className), function (e) {
			e.remove();
		});
	};

	/**
	 * Diff Table Component
	 *
	 * @param state
	 * @returns {{name: string, create: function}}
	 * @constructor
	 */
	var DiffTableComponent = function (state) {
		return {
			name: 'diff-table-component',
			create: function () {
				removeAllExsistingDoms(this.name);

				var node = document.createDocumentFragment();

				var div = document.createElement('div');
				div.classList.add(this.name);
				node.appendChild(div);

				if (state.deployType !== 'partial' || !state.diffData.length || (state.partialConfirm && state.showDiff)) {
					return node;
				}

				// table tag
				div.appendChild((function () {
					var table = document.createElement('table');
					table.classList.add('wp-list-table', 'widefat', 'striped');

					var thead = document.createElement('thead'), td;

					td = document.createElement('td');
					td.textContent = 'Deploy';
					thead.appendChild(td);

					td = document.createElement('td');
					td.textContent = 'File';
					thead.appendChild(td);

					td = document.createElement('td');
					td.textContent = 'Status';
					thead.appendChild(td);

					table.appendChild(thead);

					var tbody = document.createElement('tbody');

					state.diffData.forEach(function (e) {
						var tr = document.createElement('tr'), td;

						td = document.createElement('td');
						var input = document.createElement('input');
						input.setAttribute('type', 'checkbox');
						input.setAttribute('name', 'partial-file-checks');
						input.setAttribute('value', e.file);
						input.setAttribute('data-status', e.status);
						if (state.checkedFiles[e.file]) {
							input.setAttribute('checked', 'checked');
						}
						input.addEventListener('change', function (e) {
							if (e.target.checked) {
								state.checkedFiles[e.target.value] = {
									file: e.target.value,
									status: e.target.dataset.status
								};
							} else {
								delete state.checkedFiles[e.target.value];
							}
							setState({});
						});

						td.appendChild(input);
						tr.appendChild(td);

						td = document.createElement('td');
						td.textContent = e.file;
						tr.appendChild(td);

						td = document.createElement('td');
						td.textContent = e.status;
						tr.appendChild(td);

						tbody.appendChild(tr);
					});

					table.appendChild(tbody);
					return table;
				})());

				if (!state.partialConfirm && state.showDiff) {
					var wrapper = document.createElement('div');
					wrapper.classList.add('partial-table-actions');
					var button = document.createElement('button');
					button.classList.add('button', 'partial-confirm-action');
					button.textContent = 'Deploy';

					button.addEventListener('click', function (e) {
						e.preventDefault();
						setState({
							partialConfirm: true
						});
					});

					wrapper.appendChild(button);
					div.appendChild(wrapper);
				}

				return node;
			},
		}
	};

	/**
	 * Confirm List Component
	 *
	 * @param state
	 * @returns {{name: string, create: function}}
	 * @constructor
	 */
	var ConfirmListComponent = function (state) {
		return {
			name: 'ConfirmListComponent',
			create: function () {
				removeAllExsistingDoms(this.name);

				var node = document.createDocumentFragment();
				var div = document.createElement('div');
				div.classList.add(this.name);
				node.appendChild(div);

				if (state.deployType !== 'partial' || !Object.keys(state.checkedFiles).length || !state.partialConfirm) {
					return node;
				}

				var ul = document.createElement('ul');

				Object.keys(state.checkedFiles).forEach(function (key) {
					var e = state.checkedFiles[key];

					var li = document.createElement('li');
					li.textContent = e.status + ': ' + e.file;
					ul.appendChild(li);
				});

				div.appendChild(ul);

				if (state.partialConfirm) {
					var button = document.createElement('button');
					button.classList.add('button');
					button.textContent = 'Back';
					button.addEventListener('click', function () {
						setState({partialConfirm: false});
					});

					div.appendChild(button);
				}

				return node;
			}
		};
	};

	/**
	 *
	 * @param state
	 * @returns {{name: string, create: (function(): HTMLDivElement)}}
	 * @constructor
	 */
	var DeployTypeRadioComponent = function (state) {
		return {
			name: 'DeployTypeRadioComponent',
			create: function () {
				removeAllExsistingDoms(this.name);

				var node = document.createDocumentFragment();
				var div = document.createElement('div');
				div.classList.add(this.name);
				node.appendChild(div);

				[
					{
						text: 'Deploy All Files',
						type: 'whole'
					},
					{
						text: 'Specify Files to Deploy',
						type: 'partial'
					}
				].forEach(function (data) {
					var label = document.createElement('label');
					var input = document.createElement('input');
					input.setAttribute('type', 'radio');
					input.setAttribute('name', 'deploy-type');
					input.setAttribute('value', data.type);
					if (state.deployType === data.type) {
						input.setAttribute('checked', 'checked');
					}
					input.addEventListener('change', function (e) {
						setState({
							deployType: e.target.value
						});
					});
					label.appendChild(input);
					label.appendChild(document.createTextNode(data.text));

					div.appendChild(label);
				});

				return node;
			}
		}
	};


	/**
	 *
	 * @param state
	 * @returns {{name: string, create: (function(): DocumentFragment)}}
	 * @constructor
	 */
	var DeployScheduleButtonComponent = function (state) {
		return {
			name: 'DiffScheduleButton',
			create: function () {
				removeAllExsistingDoms(this.name);

				var node = document.createDocumentFragment();
				var div = document.createElement('div');
				div.classList.add(this.name);
				node.appendChild(div);

				if (state.deployType !== 'whole' && !state.partialConfirm) {
					return node;
				}

				var label = document.createElement('label');
				var text = document.createTextNode('日付');
				var input = document.createElement('input');
				input.setAttribute('type', 'date');
				input.setAttribute('name', 'schedule_date');
				label.appendChild(text);
				label.appendChild(input);
				div.appendChild(label);

				label = document.createElement('label');
				text = document.createTextNode('時間');
				input = document.createElement('input');
				input.setAttribute('type', 'time');
				input.setAttribute('name', 'schedule_time');
				label.appendChild(text);
				label.appendChild(input);
				div.appendChild(label);

				var button = document.createElement('button');
				button.setAttribute('type', 'submit');
				button.classList.add('button', 'button-primary');
				button.textContent = 'デプロイ';

				if (state.deployType === 'partial') {
					button.addEventListener('click', function (e) {
						e.preventDefault();

						var date = document.querySelector('[name="schedule_date"]').value;
						var time = document.querySelector('[name="schedule_time"]').value;

						var requestData = partialScheduleDeployData;

						jQuery.post(requestData.url, {
							action: requestData.action,
							date: date,
							time: time,
							files: state.checkedFiles
						})
							.done(function () {
								alert('succeeded');
								location.reload();
							})
							.fail(function (e) {
								alert(e.responseText);
							});
					});
				} else {
					button.addEventListener('click', function (e) {
						e.preventDefault();

						var date = document.querySelector('[name="schedule_date"]').value;
						var time = document.querySelector('[name="schedule_time"]').value;

						var requestData = scheduleDeployData;

						jQuery.post(requestData.url, {
							action: requestData.action,
							date: date,
							time: time
						})
							.done(function () {
								alert('succeeded');
								location.reload();
							})
							.fail(function (e) {
								alert(e.responseText);
							});
					});
				}
				div.appendChild(button);

				return node;
			}
		}
	};

	/**
	 *
	 * @param state
	 * @returns {{name: string, create: (function(): DocumentFragment)}}
	 * @constructor
	 */
	var DiffActionsComponent = function (state) {
		return {
			name: 'DiffActionsComponent',
			create: function () {
				removeAllExsistingDoms(this.name);

				var node = document.createDocumentFragment();
				var div = document.createElement('div');
				div.classList.add(this.name);
				node.appendChild(div);

				if (state.deployType !== 'partial') {
					return node;
				}

				var button = document.createElement('button');
				button.classList.add('button-primary');
				button.addEventListener('click', function (e) {
					e.preventDefault();
					var requestData = getCurrentDiffsData;

					jQuery.post(requestData.url, {
						action: requestData.action
					}).done(function (e) {
						setState({
							diffData: e,
							showDiff: true
						});
					}).fail(function () {
						alert('failed');
					})
				});
				button.textContent = '差分取得';
				div.appendChild(button);

				return node;
			}
		}
	};

	Array.prototype.forEach.call(document.querySelectorAll('.smde-unschedule-deploy'), function (e) {
		e.addEventListener('click', function (e) {
			e.preventDefault();

			var timestamp = e.target.dataset.timestamp;

			if (!timestamp) {
				alert('no timestamp');
				return;
			}

			var requestData = unscheduleDeployData;

			jQuery.post(requestData.url, {
				action: requestData.action,
				timestamp: timestamp
			}).done(function () {
				alert('succeeded');
				e.target.parentNode.remove();
			}).fail(function () {
				alert('failed');
			});
		});
	});

	// initial render
	setState({});
});
