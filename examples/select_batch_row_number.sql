
-- ROW_NUMBER() batch window example
WITH src AS (
  SELECT
    ROW_NUMBER() OVER (ORDER BY [Id]) AS rn,
    *
  FROM dbo.LargeTable WITH (NOLOCK)
)
SELECT *
FROM src
WHERE rn BETWEEN ? AND ?;  -- sqlsrv params: start, end
