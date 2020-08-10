/**
 * External dependencies.
 */
import { registerFieldType } from "@carbon-fields/core";

/**
 * Internal dependencies.
 */
import "./style.scss";
import EDDLicenseField from "./main";

registerFieldType("edd_license", EDDLicenseField);
