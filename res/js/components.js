window.addEventListener('load', function() {})
;(function(window) {
	const h = hyperapp.h

	const getMessage = msg => {
		if (!window.DeployExtraMessages) {
			return msg
		}
		return window.DeployExtraMessages[msg] || msg
	}

	const components = {
		Loader: () => (state, actions) => {
			if (!state.processing) {
				return false
			}

			return h('div', { class: 'smde-loader-wrapper' }, [
				h('div', { class: 'smde-loader' })
			])
		},
		DeployScheduleSelector: () => (state, actions) => {
			const setScheduleDate = actions.setScheduleDate
			const setScheduleTime = actions.setScheduleTime
			const switchScheduled = actions.switchScheduled
			const requestDeploy = actions.requestDeploy

			const deployType = state.deployType
			const isScheduleDeploy = state.isScheduleDeploy
			const now = !isScheduleDeploy

			return h('div', {}, [
				h('label', { class: 'schedule-deploy-switch-select' }, [
					h('input', {
						type: 'checkbox',
						onchange: e => {
							switchScheduled(e.target.checked)
						}
					}),
					getMessage('Schedule Deploy')
				]),
				h('label', { class: 'schedule-deploy-date-input' }, [
					getMessage('Date'),
					h('input', {
						type: 'date',
						name: 'schedule_date',
						disabled: !isScheduleDeploy,
						onchange: e => {
							setScheduleDate(e.target.value)
						}
					})
				]),
				h('label', { class: 'schedule-deploy-time-input' }, [
					getMessage('Time'),
					h('input', {
						type: 'time',
						name: 'schedule_time',
						disabled: !isScheduleDeploy,
						onchange: e => {
							setScheduleTime(e.target.value)
						}
					})
				]),
				h(
					'button',
					{
						type: 'submit',
						class: 'button button-primary deploy-button',
						onclick: e => {
							e.preventDefault()
							requestDeploy({ now })
						}
					},
					isScheduleDeploy
						? getMessage('Reserve')
						: getMessage('Reserve at current time')
				)
			])
		},
		DeployTypeRadio: () => (state, actions) => {
			const deployType = state.deployType
			const changeDeployType = actions.changeDeployType

			const makeRadio = (text, type, currentType) => {
				return h('label', { class: 'deploy-type-radio-label' }, [
					h('input', {
						type: 'radio',
						name: 'deploy-type',
						checked: currentType === type,
						onchange: e => {
							changeDeployType(type)
						}
					}),
					text
				])
			}

			return h('div', { class: 'deploy-type-radio-wrapper' }, [
				makeRadio(getMessage('Deploy All Files'), 'whole', deployType),
				makeRadio(
					getMessage('Specify Files to Deploy'),
					'partial',
					deployType
				)
			])
		},
		DiffListTable: () => (state, actions) => {
			const diffData = state.diffData
			const checkedFiles = state.checkedFiles
			const checkDiffFile = actions.checkDiffFile
			const uncheckDiffFile = actions.uncheckDiffFile

			return h('div', {}, [
				h('table', { class: 'wp-list-table widefat striped' }, [
					h('thead', {}, [
						h('td', {}, getMessage('Deploy')),
						h('td', {}, getMessage('File')),
						h('td', {}, getMessage('Status'))
					]),
					h(
						'tbody',
						{},
						diffData.map(line => [
							h('tr', {}, [
								h('td', {}, [
									h('input', {
										type: 'checkbox',
										name: 'partial-file-checks',
										checked: checkedFiles.has(
											line.file_path
										),
										onchange: e => {
											if (e.target.checked) {
												console.log(line)
												checkDiffFile(line)
											} else {
												uncheckDiffFile(line.file_path)
											}
										}
									})
								]),
								h('td', {}, line.file_path),
								h('td', {}, getMessage(line.action))
							])
						])
					)
				])
			])
		},
		ConfirmList: () => (state, actions) => {
			const checkedFiles = state.checkedFiles
			return h('div', {}, [
				h(
					'ul',
					{},
					Array.from(checkedFiles, ([key, value]) => {
						return [h('li', {}, value.file_path)]
					})
				)
			])
		},
		GetProductionDiffButton: () => (state, actions) => {
			const compareDiff = actions.compareDiff

			return h('div', { class: 'compare-diff-button-wrapper' }, [
				h(
					'button',
					{
						class: 'button button-primary',
						onclick: e => {
							e.preventDefault()
							compareDiff()
						}
					},
					getMessage('Compare Diff')
				)
			])
		},
		DiffListConfirmButton: opts => {
			const onclick = opts.onclick
			return h('div', { class: 'partial-table-actions' }, [
				h(
					'button',
					{
						class: 'button partial-confirm-action',
						onclick: e => {
							e.preventDefault()
							onclick('confirm')
						}
					},
					getMessage('Next')
				)
			])
		},
		DiffListConfirmBackButton: opts => {
			const onclick = opts.onclick
			return h('div', { class: 'partial-table-actions' }, [
				h(
					'button',
					{
						class: 'button',
						onclick: e => {
							e.preventDefault()
							onclick('file-list')
						}
					},
					getMessage('Back')
				)
			])
		}
	}

	window.smdeComponents = components
})(window)
