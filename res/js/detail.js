document.addEventListener('DOMContentLoaded', function() {
	const getMessage = msg => {
		if (!window.DeployExtraMessages) {
			return msg
		}
		return window.DeployExtraMessages[msg] || msg
	}

	const getCheckedFilesFromPHP = () =>
		window.currentDeployDiffData &&
		window.currentDeployDiffData.reduce(
			(a, c) => a.set(c.file_path, c),
			new Map()
		)

	const getDeployFileExistsFromPHP = () =>
		window.currentDeployData && window.currentDeployData.exists === '1'
			? true
			: false

	const getDeployTypeFromPHP = () =>
		window.currentDeployData ? window.currentDeployData.type : null

	const h = hyperapp.h
	const app = hyperapp.app

	const components = window.smdeComponents

	const defaultState = {
		scheduleDate: null,
		scheduleTime: null,
		checkedFiles: new Map(),
		processing: false,

		// objects passed from php
		checkedFiles: getCheckedFilesFromPHP(),
		deployFileExists: getDeployFileExistsFromPHP(),
		deployId: window.currentDeployData ? window.currentDeployData.id : null,
		deployType: getDeployTypeFromPHP(),
		scheduleDeployData: window.scheduleDeployData,
		partialScheduleDeployData: window.partialScheduleDeployData
	}

	const view = (state, actions) => {
		console.log(state)

		const existFiles = state.deployFileExists
		return h('div', {}, [
			components.Loader,
			existFiles ? components.DeployScheduleSelector : ''
		])
	}

	app(
		defaultState,
		window.smdeActions,
		view,
		document.querySelector('.smde-deploy-app')
	)

	Array.prototype.forEach.call(
		document.querySelectorAll('.smde-unschedule-deploy'),
		function(e) {
			e.addEventListener('click', function(e) {
				e.preventDefault()

				const timestamp = e.target.dataset.timestamp

				if (!timestamp) {
					alert(getMessage('no timestamp'))
					return
				}

				const requestData = unscheduleDeployData

				jQuery
					.post(requestData.url, {
						action: requestData.action,
						timestamp: timestamp
					})
					.done(function() {
						alert(getMessage('succeeded'))
						e.target.parentNode.remove()
					})
					.fail(function() {
						alert(getMessage('failed'))
					})
			})
		}
	)
})
