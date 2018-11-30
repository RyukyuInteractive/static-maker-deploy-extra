;(() => {
	const getMessage = msg => {
		if (!window.DeployExtraMessages) {
			return msg
		}
		return window.DeployExtraMessages[msg] || msg
	}

	window.smdeActions = {
		setProcessing: processing => state => ({ processing }),
		setScheduleDate: value => state => ({
			scheduleDate: value
		}),
		setScheduleTime: value => state => ({
			scheduleTime: value
		}),
		switchScheduled: value => state => ({
			isScheduleDeploy: value
		}),
		requestDeploy: (opts = {}) => async (state, actions) => {
			date = opts.date || state.scheduleDate
			time = opts.time || state.scheduleTime
			deploy = opts.deploy || state.deployId || null

			if (state.processing) {
				return
			}

			if (!opts.now && (!date || !time)) {
				alert(getMessage('Missing Required Arguments'))
				return
			}

			const requestData =
				state.deployType === 'partial'
					? state.partialScheduleDeployData
					: state.scheduleDeployData

			actions.setProcessing(true)

			jQuery
				.post(requestData.url, {
					action: requestData.action,
					files: Array.from(state.checkedFiles).reduce(
						(a, [k, v]) => Object.assign(a, { [k]: v }),
						{}
					),
					now: opts.now || null,
					date,
					time,
					deploy
				})
				.done(() => {
					actions.setProcessing(false)
					alert(getMessage('The new deployment has been reserved'))
				})
				.fail(e => {
					actions.setProcessing(false)
					alert(e.responseText)
				})
		},
		compareDiff: () => (state, actions) =>
			new Promise((resolve, reject) => {
				const requestData = getCurrentDiffsData

				if (state.processing) {
					return
				}

				actions.setProcessing(true)

				jQuery
					.post(requestData.url, {
						action: requestData.action
					})
					.done(e => {
						actions.setProcessing(false)

						resolve(state)

						if (e.length) {
							actions.updateDiffFiles(e)
						} else {
							alert(getMessage('The production is latest'))
						}
					})
					.fail(function() {
						actions.setProcessing(false)
						alert(getMessage(getMessage('failed')))
						reject(new Error('failecd to compare'))
					})
			}),
		updateDiffFiles: diffData => state => ({ diffData }),

		changeDeployType: deployType => state => ({ deployType }),

		checkDiffFile: file => state => ({
			checkedFiles: state.checkedFiles.set(file.file_path, file)
		}),

		uncheckDiffFile: filePath => state => ({
			checkedFiles: state.checkedFiles.delete(filePath)
		}),

		setPartialView: partialView => state => ({ partialView })
	}
})()
