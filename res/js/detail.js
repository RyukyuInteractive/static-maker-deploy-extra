document.addEventListener("DOMContentLoaded", function() {
	
	var getParams = location.search.split("&").reduce(function(a, c) {
		console.log(a, c);
		var s = c.split("=");
		a[s[0]] = s[1];
		return a;
	}, {});

	var setState = (function() {
		var state = {};

		return function(newState) {
			// merge state
			state = Object.keys(newState).reduce(function(a, c) {
				a[c] = newState[c];
				return a;
			}, state);

			applyState(state);
		};
	})();

	var applyState = function(state) {
		console.log(state);

		var deployActionWrapper = document.querySelector(
			".smde-deploy-action-wrapper"
		);

		var onDeploy = function(data) {
			var requestData = scheduleDeployData;

			jQuery
				.post(requestData.url, {
					action: requestData.action,
					date: data.date,
					time: data.time,
					deploy: getParams["deploy"]
				})
				.done(function() {
					alert("succeeded");
					location.reload();
				})
				.fail(function(e) {
					alert(e.responseText);
				});
		};

		if (deployActionWrapper) {
			deployActionWrapper.appendChild(
				smdeComponents
					.DeployScheduleButtonComponent(setState, state)
					.create(onDeploy)
			);
		}
	};

	setState({});
});
