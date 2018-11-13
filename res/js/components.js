window.addEventListener('load', function() {})
;(function(window) {
	var h = hyperapp.h

	var removeAllExsistingDoms = function(className) {
		Array.prototype.forEach.call(
			document.querySelectorAll('.' + className),
			function(e) {
				e.remove()
			}
		)
	}

	window.smdeComponents = {
		HDeployScheduleSelector: function(opts) {
			var setScheduleDate = opts.setScheduleDate
			var setScheduleTime = opts.setScheduleTime
			var requestDeploy = opts.requestDeploy

			return h('div', {}, [
				h('label', {}, [
					'Date',
					h('input', {
						type: 'date',
						name: 'schedule_date',
						onchange: function(e) {
							setScheduleDate(e.target.value)
						}
					})
				]),
				h('label', {}, [
					'Time',
					h('input', {
						type: 'time',
						name: 'schedule_time',
						onchange: function(e) {
							setScheduleTime(e.target.value)
						}
					})
				]),
				h(
					'button',
					{
						type: 'submit',
						class: 'button button-primary',
						onclick: function(e) {
							e.preventDefault()
							requestDeploy()
						}
					},
					'Deploy'
				)
			])
		},

		/**
		 * Diff Table Component
		 *
		 * @param state
		 * @returns {{name: string, create: function}}
		 * @constructor
		 */
		DiffTableComponent: function(setState, state) {
			return {
				name: 'diff-table-component',
				create: function() {
					removeAllExsistingDoms(this.name)

					var node = document.createDocumentFragment()

					var div = document.createElement('div')
					div.classList.add(this.name)
					node.appendChild(div)

					if (
						state.deployType !== 'partial' ||
						!state.diffData.length ||
						(state.partialConfirm && state.showDiff)
					) {
						return node
					}

					// table tag
					div.appendChild(
						(function() {
							var table = document.createElement('table')
							table.classList.add(
								'wp-list-table',
								'widefat',
								'striped'
							)

							var thead = document.createElement('thead'),
								td

							td = document.createElement('td')
							td.textContent = 'Deploy'
							thead.appendChild(td)

							td = document.createElement('td')
							td.textContent = 'File'
							thead.appendChild(td)

							td = document.createElement('td')
							td.textContent = 'Status'
							thead.appendChild(td)

							table.appendChild(thead)

							var tbody = document.createElement('tbody')

							state.diffData.forEach(function(e) {
								var tr = document.createElement('tr'),
									td

								td = document.createElement('td')
								var input = document.createElement('input')
								input.setAttribute('type', 'checkbox')
								input.setAttribute(
									'name',
									'partial-file-checks'
								)
								input.setAttribute('value', e.file)
								input.setAttribute('data-status', e.status)
								if (state.checkedFiles[e.file]) {
									input.setAttribute('checked', 'checked')
								}
								input.addEventListener('change', function(e) {
									if (e.target.checked) {
										state.checkedFiles[e.target.value] = {
											file: e.target.value,
											status: e.target.dataset.status
										}
									} else {
										delete state.checkedFiles[
											e.target.value
										]
									}
									setState({})
								})

								td.appendChild(input)
								tr.appendChild(td)

								td = document.createElement('td')
								td.textContent = e.file
								tr.appendChild(td)

								td = document.createElement('td')
								td.textContent = e.status
								tr.appendChild(td)

								tbody.appendChild(tr)
							})

							table.appendChild(tbody)
							return table
						})()
					)

					if (!state.partialConfirm && state.showDiff) {
						var wrapper = document.createElement('div')
						wrapper.classList.add('partial-table-actions')
						var button = document.createElement('button')
						button.classList.add('button', 'partial-confirm-action')
						button.textContent = 'Deploy'

						button.addEventListener('click', function(e) {
							e.preventDefault()
							setState({
								partialConfirm: true
							})
						})

						wrapper.appendChild(button)
						div.appendChild(wrapper)
					}

					return node
				},
				remove() {
					removeAllExsistingDoms(this.name)
				}
			}
		},
		/**
		 * Confirm List Component
		 *
		 * @param state
		 * @returns {{name: string, create: function}}
		 * @constructor
		 */
		ConfirmListComponent: function(setState, state) {
			return {
				name: 'ConfirmListComponent',
				create: function() {
					removeAllExsistingDoms(this.name)

					var node = document.createDocumentFragment()
					var div = document.createElement('div')
					div.classList.add(this.name)
					node.appendChild(div)

					if (
						state.deployType !== 'partial' ||
						!Object.keys(state.checkedFiles).length ||
						!state.partialConfirm
					) {
						return node
					}

					var ul = document.createElement('ul')

					Object.keys(state.checkedFiles).forEach(function(key) {
						var e = state.checkedFiles[key]

						var li = document.createElement('li')
						li.textContent = e.status + ': ' + e.file
						ul.appendChild(li)
					})

					div.appendChild(ul)

					if (state.partialConfirm) {
						var button = document.createElement('button')
						button.classList.add('button')
						button.textContent = 'Back'
						button.addEventListener('click', function() {
							setState({ partialConfirm: false })
						})

						div.appendChild(button)
					}

					return node
				},
				remove() {
					removeAllExsistingDoms(this.name)
				}
			}
		},
		/**
		 *
		 * @param state
		 * @returns {{name: string, create: (function(): HTMLDivElement)}}
		 * @constructor
		 */
		DeployTypeRadioComponent: function(setState, state) {
			return {
				name: 'DeployTypeRadioComponent',
				create: function() {
					removeAllExsistingDoms(this.name)

					var node = document.createDocumentFragment()
					var div = document.createElement('div')
					div.classList.add(this.name)
					node.appendChild(div)
					;[
						{
							text: 'Deploy All Files',
							type: 'whole'
						},
						{
							text: 'Specify Files to Deploy',
							type: 'partial'
						}
					].forEach(function(data) {
						var label = document.createElement('label')
						var input = document.createElement('input')
						input.setAttribute('type', 'radio')
						input.setAttribute('name', 'deploy-type')
						input.setAttribute('value', data.type)
						if (state.deployType === data.type) {
							input.setAttribute('checked', 'checked')
						}
						input.addEventListener('change', function(e) {
							setState({
								deployType: e.target.value
							})
						})
						label.appendChild(input)
						label.appendChild(document.createTextNode(data.text))

						div.appendChild(label)
					})

					return node
				},
				remove() {
					removeAllExsistingDoms(this.name)
				}
			}
		},
		/**
		 *
		 * @param state
		 * @returns {{name: string, create: (function(): DocumentFragment)}}
		 * @constructor
		 */
		DeployScheduleButtonComponent: function(setState, state) {
			return {
				name: 'DiffScheduleButton',
				create: function(onDeploy) {
					onDeploy = onDeploy || function() {}

					removeAllExsistingDoms(this.name)

					var node = document.createDocumentFragment()
					var div = document.createElement('div')
					div.classList.add(this.name)
					node.appendChild(div)

					var label = document.createElement('label')
					var text = document.createTextNode('Date')
					var input = document.createElement('input')
					input.setAttribute('type', 'date')
					input.setAttribute('name', 'schedule_date')
					label.appendChild(text)
					label.appendChild(input)
					div.appendChild(label)

					label = document.createElement('label')
					text = document.createTextNode('Time')
					input = document.createElement('input')
					input.setAttribute('type', 'time')
					input.setAttribute('name', 'schedule_time')
					label.appendChild(text)
					label.appendChild(input)
					div.appendChild(label)

					var button = document.createElement('button')
					button.setAttribute('type', 'submit')
					button.classList.add('button', 'button-primary')
					button.textContent = 'Deploy'

					button.addEventListener('click', function(e) {
						e.preventDefault()

						var date = document.querySelector(
							'[name="schedule_date"]'
						).value
						var time = document.querySelector(
							'[name="schedule_time"]'
						).value

						onDeploy({
							date: date,
							time: time
						})
					})

					div.appendChild(button)

					return node
				},
				remove() {
					removeAllExsistingDoms(this.name)
				}
			}
		},
		/**
		 *
		 * @param state
		 * @returns {{name: string, create: (function(): DocumentFragment)}}
		 * @constructor
		 */
		DiffActionsComponent: function(setState, state) {
			return {
				name: 'DiffActionsComponent',
				create: function() {
					removeAllExsistingDoms(this.name)

					var node = document.createDocumentFragment()
					var div = document.createElement('div')
					div.classList.add(this.name)
					node.appendChild(div)

					var button = document.createElement('button')
					button.classList.add('button-primary')
					button.addEventListener('click', function(e) {
						e.preventDefault()
						var requestData = getCurrentDiffsData

						jQuery
							.post(requestData.url, {
								action: requestData.action
							})
							.done(function(e) {
								setState({
									diffData: e,
									showDiff: true
								})
							})
							.fail(function() {
								alert('failed')
							})
					})
					button.textContent = '差分取得'
					div.appendChild(button)

					return node
				},
				remove() {
					removeAllExsistingDoms(this.name)
				}
			}
		}
	}
})(window)
