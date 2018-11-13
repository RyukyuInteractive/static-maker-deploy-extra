document.addEventListener('DOMContentLoaded', function() {
	var h = hyperapp.h
	var app = hyperapp.app

	var compoments = window.smdeComponents

	var setState = (function() {
		var state = {
			deployType: 'whole',
			partialConfirm: false,
			showDiff: false,
			diffData: [],
			checkedFiles: {}
		}

		return function(newState) {
			// merge state
			state = Object.keys(newState).reduce(function(a, c) {
				a[c] = newState[c]
				return a
			}, state)

			applyState(state)
		}
	})()

	/**
	 * called every time the state is updated
	 */
	var applyState = function(state) {
		console.log(state)

		// render components

		// fetching the diff button component
		if (state.deployType === 'partial') {
			document
				.querySelector('.diff-actions')
				.appendChild(
					smdeComponents
						.DiffActionsComponent(setState, state)
						.create()
				)
		} else {
			smdeComponents.DiffActionsComponent(setState, state).remove()
		}

		document
			.querySelector('.diff-table-output')
			.appendChild(
				smdeComponents.DiffTableComponent(setState, state).create()
			)
		document
			.querySelector('.diff-confirm-output')
			.appendChild(
				smdeComponents.ConfirmListComponent(setState, state).create()
			)
		document
			.querySelector('.deploy-type-buttons')
			.appendChild(
				smdeComponents
					.DeployTypeRadioComponent(setState, state)
					.create()
			)
	}

	Array.prototype.forEach.call(
		document.querySelectorAll('.smde-unschedule-deploy'),
		function(e) {
			e.addEventListener('click', function(e) {
				e.preventDefault()

				var timestamp = e.target.dataset.timestamp

				if (!timestamp) {
					alert('no timestamp')
					return
				}

				var requestData = unscheduleDeployData

				jQuery
					.post(requestData.url, {
						action: requestData.action,
						timestamp: timestamp
					})
					.done(function() {
						alert('succeeded')
						e.target.parentNode.remove()
					})
					.fail(function() {
						alert('failed')
					})
			})
		}
	)

	const defaultState = {
		scheduleDate: null,
		scheduleTime: null,
		checkedFiles: [],
		deployType: 'whole',
		partialConfirm: false,

		// objects passed from php
		scheduleDeployData,
		partialScheduleDeployData
	}

	// TODO: hyperapp でのコンポーネントベースの条件分岐をどうするか

	const actions = {
		setScheduleDate: value => state =>
			Object.assign({}, state, {
				scheduleDate: value
			}),
		setScheduleTime: value => state =>
			Object.assign({}, state, {
				scheduleTime: value
			}),
		requestDeploy: (date, time) => async (state, actions) => {
			date = date || state.scheduleDate
			time = time || state.scheduleTime

			if (!date || !time) {
				throw new Error('Missing Required Arguments')
			}

			const requestData =
				state.deployType === 'partial'
					? state.partialScheduleDeployData
					: state.scheduleDeployData

			jQuery
				.post(requestData.url, {
					action: requestData.action,
					files: state.checkedFiles,
					date,
					time
				})
				.done(() => {
					alert('succeeded')
					location.reload()
				})
				.fail(e => {
					alert(e.responseText)
				})
		}
	}

	var renderDeployScheduleSelector = (state, actions) => {
		return state.deployType === 'whole' || state.partialConfirm
			? [
					compoments.HDeployScheduleSelector,
					Object.assign(state, actions)
			  ]
			: []
	}

	var view = (state, actions) => {
		console.log(state)

		return h('div', {}, [renderDeployScheduleSelector(state, actions)])
	}

	app(defaultState, actions, view, document.querySelector('.smde-deploy-app'))

	// initial render
	setState({})
})
