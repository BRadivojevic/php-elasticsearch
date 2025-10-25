
-- OFFSET/FETCH batch window example (make sure ORDER BY uses an indexed, stable column)
SELECT *
FROM dbo.LargeTable WITH (NOLOCK)
ORDER BY [Id]
OFFSET ? ROWS FETCH NEXT ? ROWS ONLY; -- sqlsrv params: offset, limit
