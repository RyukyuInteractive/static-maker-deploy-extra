document.addEventListener("DOMContentLoaded", function() {
	var setState = (function() {
		var state = {
			deployType: "whole",
			partialConfirm: false,
			showDiff: false,
			diffData: [],
			checkedFiles: {}
		};

		return function(newState) {
			// merge state
			state = Object.keys(newState).reduce(function(a, c) {
				a[c] = newState[c];
				return a;
			}, state);

			applyState(state);
		};
	})();

	/**
	 * called every time the state is updated
	 */
	var applyState = function(state) {
		console.log(state);

		// render components

		// schedule button components
		if (state.deployType === "whole" || state.partialConfirm) {
			var onDeploy = function(data) {
				var requestData;

				if (state.deployType === "partial") {
					requestData = partialScheduleDeployData;
				} else {
					requestData = scheduleDeployData;
				}

				jQuery
					.post(requestData.url, {
						action: requestData.action,
						date: data.date,
						time: data.time,
						files: state.checkedFiles
					})
					.done(function() {
						alert("succeeded");
						location.reload();
					})
					.fail(function(e) {
						alert(e.responseText);
					});
			};
			document
				.querySelector(".smde-deploy-form-wrapper")
				.appendChild(
					smdeComponents
						.DeployScheduleButtonComponent(setState, state)
						.create(onDeploy)
				);
		} else {
			smdeComponents.DeployScheduleButtonComponent().remove();
		}

		// fetching the diff button component
		if (state.deployType === "partial") {
			document
				.querySelector(".diff-actions")
				.appendChild(
					smdeComponents
						.DiffActionsComponent(setState, state)
						.create()
				);
		} else {
			smdeComponents.DiffActionsComponent(setState, state).remove();
		}

		document
			.querySelector(".diff-table-output")
			.appendChild(
				smdeComponents.DiffTableComponent(setState, state).create()
			);
		document
			.querySelector(".diff-confirm-output")
			.appendChild(
				smdeComponents.ConfirmListComponent(setState, state).create()
			);
		document
			.querySelector(".deploy-type-buttons")
			.appendChild(
				smdeComponents
					.DeployTypeRadioComponent(setState, state)
					.create()
			);
	};

	Array.prototype.forEach.call(
		document.querySelectorAll(".smde-unschedule-deploy"),
		function(e) {
			e.addEventListener("click", function(e) {
				e.preventDefault();

				var timestamp = e.target.dataset.timestamp;

				if (!timestamp) {
					alert("no timestamp");
					return;
				}

				var requestData = unscheduleDeployData;

				jQuery
					.post(requestData.url, {
						action: requestData.action,
						timestamp: timestamp
					})
					.done(function() {
						alert("succeeded");
						e.target.parentNode.remove();
					})
					.fail(function() {
						alert("failed");
					});
			});
		}
	);

	// initial render
	setState({});
});
