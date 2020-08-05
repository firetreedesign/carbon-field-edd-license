/**
 * External dependencies.
 */
import { Component } from "@wordpress/element";

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
			<div>
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
					type="button"
					className="dashicons-before dashicons-no-alt"
				></button>
			</div>
		);
	}
}

export default FTEDDLicenseField;
