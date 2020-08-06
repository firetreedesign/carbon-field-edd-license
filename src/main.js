/**
 * External dependencies.
 */
import { Component, Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

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
					name={`${name}_nonce`}
					value={ft_edd_license.nonce}
				/>
				<div className="cf-ft_edd_license__container">
					<input
						type="text"
						id={id}
						name={name}
						value={value}
						className="cf-ft_edd_license__input"
						onChange={this.handleChange}
						{...field.attributes}
					/>
					<button
						type="submit"
						name={`${name}_activate_license`}
						className="button"
					>
						{__("Activate License")}
					</button>
				</div>
			</Fragment>
		);
	}
}

export default FTEDDLicenseField;
