/**
 * External dependencies.
 */
import { registerFieldType } from "@carbon-fields/core";

/**
 * Internal dependencies.
 */
import "./style.scss";
import FTEDDLicenseField from "./main";

registerFieldType("ft_edd_license", FTEDDLicenseField);
