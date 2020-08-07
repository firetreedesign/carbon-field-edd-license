/**
 * External dependencies.
 */
import { Component, Fragment } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import { dateI18n } from "@wordpress/date";

class FTEDDLicenseField extends Component {
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

	licenseActive = (status) => {
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

	handleActivation = (e) => {
		e.preventDefault();
		alert("Activate");
		console.log(ajaxurl);
	};

	handleDeactivation = (e) => {
		e.preventDefault();
		alert("Deactivate");
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
				<div className="cf-ft_edd_license__container">
					<input
						type="text"
						id={id}
						name={name}
						value={value}
						readOnly={this.licenseActive(field.status)}
						className="cf-ft_edd_license__input"
						onChange={this.handleChange}
						{...field.attributes}
					/>
					{!field.license && (
						<button type="button" disabled="disabled">
							{__("Save before activating")}
						</button>
					)}
					{field.license && !this.licenseActive(field.status) && (
						<button
							type="button"
							name={`${field.name}_activate_license`}
							className="button"
							onClick={this.handleActivation}
						>
							{__("Activate License")}
						</button>
					)}
					{field.license && this.licenseActive(field.status) && (
						<button
							type="button"
							name={`${field.name}_deactivate_license`}
							className="button"
							onClick={this.handleDeactivation}
						>
							{__("Deactivate License")}
						</button>
					)}
				</div>
				{field.status &&
					field.status.license &&
					"valid" === field.status.license &&
					field.status.expires &&
					"lifetime" === field.status.expires && (
						<p>{__("Your license key never expires.")}</p>
					)}
				{field.status &&
					field.status.license &&
					"valid" === field.status.license &&
					field.status.expires &&
					"lifetime" !== field.status.expires && (
						<p>
							{sprintf(
								"Your license key expires on %s.",
								dateI18n(
									field.date_format,
									field.status.expires
								)
							)}
						</p>
					)}
			</Fragment>
		);
	}
}

export default FTEDDLicenseField;
