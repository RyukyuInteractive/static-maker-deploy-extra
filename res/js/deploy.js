document.addEventListener("DOMContentLoaded", function() {
	window.state = {
		deployType: "whole",
		partialConfirm: false,
		showDiff: false,
		diffData: [],
		checkedFiles: {}
	};

	var setState = function(newState) {
		// merge state
		state = Object.keys(newState).reduce(function(a, c) {
			a[c] = newState[c];
			return a;
		}, state);

		applyState();
	};

	/**
	 * called every time the state is updated
	 */
	var applyState = function() {
		console.log(state);

		// render components
		document
			.querySelector(".smde-deploy-form-wrapper")
			.appendChild(
				smdeComponents
					.DeployScheduleButtonComponent(setState, state)
					.create()
			);
		document
			.querySelector(".diff-actions")
			.appendChild(
				smdeComponents.DiffActionsComponent(setState, state).create()
			);
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
