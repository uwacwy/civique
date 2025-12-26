export const setCause = (cause) => (error) => {
	if (error instanceof Error) {
		error.cause = cause;
	}
	throw error;
};
