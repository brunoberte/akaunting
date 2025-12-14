import Alert from '@mui/material/Alert';

type ErrorListProps = {
  errors: Record<string, string>;
};

export function ErrorList({ errors }: ErrorListProps) {
  if (Object.values(errors).length === 0) return null;

  return (
    <Alert variant="filled" severity="error">
        <ul>
          {Object.values(errors).map((error, idx) =>
            error ? <li key={idx}>{error}</li> : null
          )}
        </ul>
    </Alert>
  );
}
