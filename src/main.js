/**
 * External dependencies.
 */
import { Component, Fragment } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import { dateI18n } from "@wordpress/date";

class EDDLicenseField extends Component {
	constructor(props) {
		super(props);
		this.state = {
			status: null,
			error: null,
			active: false,
			loading: false,
			activateBtn: __("Activate License"),
			deactivateBtn: __("Deactivate License"),
		};
	}

	componentDidMount() {
		this.setState({
			active: this.licenseActive(),
			status: this.props.field.license_status,
		});
	}

	/**
	 * Handles the change of the input.
	 *
	 * @param  {Object} e
	 * @return {void}
	 */
	handleChange = (e) => {
		const { id, onChange } = this.props;

		onChange(id, e.target.value);
	};

	licenseActive = () => {
		const { status } = this.props.field;
		let active = false;
		if ("object" === typeof status && undefined !== status.license) {
			switch (status.license) {
				case "valid":
					active = true;
					break;
				default:
					break;
			}
		}
		return active;
	};

	handleActivation = (e, fieldName) => {
		e.preventDefault();

		let data = new FormData();
		data.append("action", fieldName + "_activate");
		data.append("_wpnonce", edd_license.nonce);

		this.setState({
			loading: true,
			activateBtn: __("Activating..."),
		});

		fetch(edd_license.ajaxurl, {
			method: "POST",
			credentials: "same-origin",
			body: data,
		})
			.then((response) => response.json())
			.then((data) => {
				if (data.error) {
					this.setState({
						status: data.status,
						error: data.error,
						active: false,
						loading: false,
						activateBtn: __("Activate License"),
					});
				} else {
					this.setState({
						status: data.status,
						error: null,
						active: true,
						loading: false,
						activateBtn: __("Activate License"),
					});
				}
			})
			.catch((error) => {
				this.setState({
					error: error,
					loading: false,
					activateBtn: __("Activate License"),
				});
			});
	};

	handleDeactivation = (e, fieldName) => {
		e.preventDefault();

		let data = new FormData();
		data.append("action", fieldName + "_deactivate");
		data.append("_wpnonce", edd_license.nonce);

		this.setState({
			loading: true,
			deactivateBtn: __("Deactivating..."),
		});

		fetch(edd_license.ajaxurl, {
			method: "POST",
			credentials: "same-origin",
			body: data,
		})
			.then((response) => response.json())
			.then((data) => {
				this.setState({
					status: data.status,
					error: data.error,
					active: false,
					loading: false,
					deactivateBtn: __("Deactivate License"),
				});
			})
			.catch((error) => {
				this.setState({
					error: error,
					loading: false,
					deactivateBtn: __("Deactivate License"),
				});
			});
	};

	/**
	 * Renders the component.
	 *
	 * @return {Object}
	 */
	render() {
		const { id, name, value, field } = this.props;

		return (
			<Fragment>
				<input
					type="hidden"
					name={field.nonce_name}
					value={field.nonce}
				/>
				<div className="cf-edd_license__container">
					<input
						type="text"
						id={id}
						name={name}
						value={value}
						readOnly={this.state.active}
						className="cf-edd_license__input"
						onChange={this.handleChange}
						{...field.attributes}
					/>
					{!field.license && (
						<button type="button" disabled="disabled">
							{__("Save before activating")}
						</button>
					)}
					{field.license && !this.state.active && (
						<button
							type="button"
							name={`${field.name}_activate_license`}
							className={`button ${
								this.state.loading ? "updating-message" : ""
							}`}
							disabled={this.state.loading}
							onClick={(e) =>
								this.handleActivation(e, field.name)
							}
						>
							{this.state.activateBtn}
						</button>
					)}
					{field.license && this.state.active && (
						<button
							type="button"
							name={`${field.name}_deactivate_license`}
							className={`button ${
								this.state.loading ? "updating-message" : ""
							}`}
							disabled={this.state.loading}
							onClick={(e) =>
								this.handleDeactivation(e, field.name)
							}
						>
							{this.state.deactivateBtn}
						</button>
					)}
				</div>
				{this.state.status && <p>{this.state.status}</p>}
				{this.state.error && (
					<p>
						{__("Error:")} {this.state.error}
					</p>
				)}
			</Fragment>
		);
	}
}

export default EDDLicenseField;
