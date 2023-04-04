import "interpolate"
import "join"

lim = 0.75

data= from(bucket: "cleanTest2")
    |> range(start: 2022-12-01T00:01:00Z, stop: 2023-05-31T23:59:00Z)
    |> filter(fn: (r) =>
        r._measurement == "sensors" and
        r._field == "weight_kg" 
    )
    |> aggregateWindow(every: 15m, fn: median)
    |> filter(fn: (r) => exists r._value)
    |> group(columns: ["key"])
    |> drop(columns: ["_start", "_stop", "_field", "_measurement"])
    |> interpolate.linear(every: 15m)
    |> duplicate(column: "_value", as: "weight_kg")
  


 data_delta_noOutlier =
 data 
  |> derivative(unit: 15m, nonNegative: false)
  |> map(
        fn: (r) => ({r with
            outlier: if r._value >= lim or r._value <= -1.0*lim then
                true
            else
                false,
        }),
  )
  
data_kg_noOutlier =
data_delta_noOutlier
  |> filter(fn: (r) =>
    r.outlier == false 
  )
  |> drop(columns: ["outlier", "weight_kg"])
  |> interpolate.linear(every: 15m)
  |> cumulativeSum()
  |> timeShift(duration: -15m)




expected= from(bucket: "cleanTest2")
    |> range(start: 2022-12-01T00:01:00Z, stop: 2023-05-31T23:59:00Z)
    |> filter(fn: (r) =>
        r._measurement == "expected" and
        r._field == "weight_kg" 
    )
    |> drop(columns: ["_start", "_stop", "_field", "_measurement"])


compare= join.full(
    left: data_kg_noOutlier,
    right: expected,
    on: (l, r) => l._time== r._time and l.key== r.key,
    as: (l, r) => {
        id = if exists l._time then l._time else r._time
        
        return {exp_weight_kg: r._value, calc_weight_kg: l._value, key:l.key, time:l._time}
    },
)

data_delta_noOutlier 

//   |> filter(fn: (r) =>
//         r._field == "weight_delta_noOutlier" 
//     )
//   |> cumulativeSum()
//   |> drop(columns: ["outlier"])
//   |> rename(columns: {_field: "weight_kg_noOutlier"})

 
  
//data_delta_noOutlier |> filter(fn: (r) => exists r.weight_delta_noOutlier)

// data_kg_noOutlier //|> filter(fn: (r) => exists r.weight_kg_noOutlier)
  
  //data |> filter(fn: (r) => exists r._value)