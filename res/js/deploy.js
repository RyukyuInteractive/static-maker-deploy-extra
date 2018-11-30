document.addEventListener('DOMContentLoaded', function() {
	const getMessage = msg => {
		if (!window.DeployExtraMessages) {
			return msg
		}
		return window.DeployExtraMessages[msg] || msg
	}

	const h = hyperapp.h
	const app = hyperapp.app

	const components = window.smdeComponents

	const defaultState = {
		partialView: 'file-list',

		isScheduleDeploy: false,
		scheduleDate: null,
		scheduleTime: null,
		checkedFiles: new Map(),
		processing: false,
		deployType: 'whole',
		partialConfirm: false,
		diffData: [],

		// objects passed from php
		scheduleDeployData: window.scheduleDeployData,
		partialScheduleDeployData: window.partialScheduleDeployData
	}

	const wholeDeployContainer = (state, actions) => {
		if (state.deployType !== 'whole') {
			return false
		}

		return [components.DeployScheduleSelector]
	}

	const partialDeployContainer = (state, actions) => {
		if (state.deployType !== 'partial') {
			return false
		}

		const isFileList = state.partialView === 'file-list'
		const isConfirm = state.partialView === 'confirm'
		const hasCheckedList = !!state.checkedFiles.size
		const hasDiffList = !!state.diffData.length

		return [
			isFileList && components.GetProductionDiffButton,
			isFileList && hasDiffList && components.DiffListTable,
			isFileList &&
				hasDiffList &&
				components.DiffListConfirmButton({
					onclick: actions.setPartialView
				}),
			isConfirm && components.ConfirmList,
			isConfirm &&
				components.DiffListConfirmBackButton({
					onclick: actions.setPartialView
				}),
			isConfirm && hasCheckedList && components.DeployScheduleSelector
		]
	}

	const view = (state, actions) => {
		console.log(state)

		return h('div', {}, [
			components.Loader,
			components.DeployTypeRadio,

			wholeDeployContainer(state, actions),
			partialDeployContainer(state, actions)
		])
	}

	app(
		defaultState,
		window.smdeActions,
		view,
		document.querySelector('.smde-deploy-app')
	)
})
